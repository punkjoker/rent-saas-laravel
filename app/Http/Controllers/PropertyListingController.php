<?php

namespace App\Http\Controllers;

use App\Models\Landlord;
use App\Models\Property;
use App\Models\PropertyListing;
use App\Models\PropertyListingImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class PropertyListingController extends Controller
{
    private function landlordId(): int
    {
        $id = Landlord::where('user_id', Auth::id())->value('id');
        abort_unless($id, 403, 'Landlord account not found.');

        return (int) $id;
    }

    public function index()
    {
        $listings = PropertyListing::whereHas('property', fn ($query) => $query
            ->where('landlord_id', $this->landlordId()))
            ->with(['property', 'images'])
            ->latest()
            ->paginate(12);

        return view('property-listings.index', compact('listings'));
    }

    public function create()
    {
        $properties = Property::where('landlord_id', $this->landlordId())
            ->whereDoesntHave('listing')
            ->orderBy('property_name')
            ->get();

        return view('property-listings.create', compact('properties'));
    }

    public function store(Request $request)
    {
        $landlordId = $this->landlordId();
        $validated = $request->validate($this->rules(true));
        $storedPaths = [];

        try {
            DB::transaction(function () use (
                $validated,
                $landlordId,
                $request,
                &$storedPaths
            ) {
                if (! empty($validated['property_id'])) {
                    $property = Property::where('landlord_id', $landlordId)
                        ->findOrFail($validated['property_id']);
                } else {
                    $property = Property::create([
                        'landlord_id' => $landlordId,
                        'property_name' => trim($validated['property_name']),
                        'property_type' => 'apartment',
                        'address' => $validated['property_address'] ?? null,
                        'town' => $validated['property_town'] ?? null,
                        'unit_prefix' => 'HSE',
                        'default_rent' => $validated['monthly_rent'],
                        'default_deposit' => 0,
                        'default_garbage_fee' => 0,
                        'is_active' => true,
                    ]);
                }

                if ($property->listing()->exists()) {
                    throw ValidationException::withMessages([
                        'property_id' => 'An advertisement already exists for this property.',
                    ]);
                }

                $slug = Str::slug($validated['title']).'-'.$property->id.'-'.Str::lower(Str::random(6));
                $listing = $property->listing()->create([
                    'slug' => $slug,
                    'title' => $validated['title'],
                    'description' => $validated['description'] ?? null,
                    'monthly_rent' => $validated['monthly_rent'],
                    'bedrooms' => $validated['bedrooms'] ?? null,
                    'bathrooms' => $validated['bathrooms'] ?? null,
                    'contact_phone' => $validated['contact_phone'] ?? null,
                    'status' => 'pending',
                ]);
                $this->storeImages($listing, $request, $storedPaths);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedPaths);
            throw $exception;
        }

        return redirect()->route('property-listings.index')
            ->with('success', 'Property advertisement submitted for administrator review.');
    }

    public function edit(PropertyListing $listing)
    {
        $this->assertOwned($listing);
        $listing->load(['property', 'images']);

        return view('property-listings.edit', compact('listing'));
    }

    public function update(Request $request, PropertyListing $listing)
    {
        $this->assertOwned($listing);
        $validated = $request->validate($this->rules(false));
        $storedPaths = [];

        try {
            DB::transaction(function () use ($listing, $validated, $request, &$storedPaths) {
                $lockedListing = PropertyListing::whereKey($listing->id)
                    ->lockForUpdate()
                    ->firstOrFail();
                $existingImages = $lockedListing->images()->count();
                $newImages = count($request->file('images', []));

                if ($existingImages + $newImages > 8) {
                    throw ValidationException::withMessages([
                        'images' => 'A property advertisement can have no more than eight photos.',
                    ]);
                }

                $lockedListing->update([
                    'title' => $validated['title'],
                    'slug' => Str::slug($validated['title']).'-'.$lockedListing->property_id,
                    'description' => $validated['description'] ?? null,
                    'monthly_rent' => $validated['monthly_rent'],
                    'bedrooms' => $validated['bedrooms'] ?? null,
                    'bathrooms' => $validated['bathrooms'] ?? null,
                    'contact_phone' => $validated['contact_phone'] ?? null,
                    'status' => 'pending',
                    'rejection_reason' => null,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                ]);

                $this->storeImages($lockedListing, $request, $storedPaths);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedPaths);
            throw $exception;
        }

        return redirect()->route('property-listings.index')
            ->with('success', 'Advertisement changes saved and submitted for review.');
    }

    public function destroyImage(PropertyListing $listing, PropertyListingImage $image)
    {
        $this->assertOwned($listing);
        abort_unless((int) $image->property_listing_id === (int) $listing->id, 404);
        abort_unless(Storage::disk('public')->delete($image->image_path), 500, 'Could not remove the listing photo.');
        $image->delete();
        $listing->update([
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);

        return back()->with('success', 'Listing photo removed.');
    }

    private function rules(bool $creating): array
    {
        return [
            'property_id' => $creating
                ? ['nullable', 'required_without:property_name', 'integer']
                : ['prohibited'],
            'property_name' => $creating
                ? ['nullable', 'required_without:property_id', 'string', 'max:255']
                : ['prohibited'],
            'property_address' => $creating
                ? ['nullable', 'string', 'max:255']
                : ['prohibited'],
            'property_town' => $creating
                ? ['nullable', 'string', 'max:255']
                : ['prohibited'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'monthly_rent' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999999999.99'],
            'bedrooms' => ['nullable', 'integer', 'between:0,50'],
            'bathrooms' => ['nullable', 'integer', 'between:0,50'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    private function assertOwned(PropertyListing $listing): void
    {
        abort_unless(
            $listing->property()->where('landlord_id', $this->landlordId())->exists(),
            404
        );
    }

    private function storeImages(
        PropertyListing $listing,
        Request $request,
        array &$storedPaths
    ): void {
        $sortOrder = (int) $listing->images()->max('sort_order') + 1;
        foreach ($request->file('images', []) as $image) {
            $path = $image->storePublicly('property-listings', 'public');
            if (! $path) {
                throw new RuntimeException('Failed to store a property listing photo.');
            }

            $storedPaths[] = $path;
            $listing->images()->create([
                'image_path' => $path,
                'sort_order' => $sortOrder++,
            ]);
        }
    }
}
