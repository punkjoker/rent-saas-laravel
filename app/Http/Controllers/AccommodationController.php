<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use App\Models\AccommodationBooking;
use App\Models\AccommodationImage;
use App\Models\Landlord;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class AccommodationController extends Controller
{
    public function index()
    {
        $accommodations = Accommodation::where('landlord_id', $this->landlordId())
            ->withCount(['images', 'bookings'])
            ->latest()
            ->paginate(15);

        return view('accommodations.index', compact('accommodations'));
    }

    public function create()
    {
        $properties = Property::where('landlord_id', $this->landlordId())
            ->orderBy('property_name')
            ->get();

        return view('accommodations.create', compact('properties'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $propertyId = $this->propertyId($validated['property_id'] ?? null);
        $paths = [];

        try {
            DB::transaction(function () use ($validated, $propertyId, $request, &$paths) {
                $accommodation = Accommodation::create([
                    'landlord_id' => $this->landlordId(),
                    'property_id' => $propertyId,
                    'title' => $validated['title'],
                    'place_name' => $validated['place_name'],
                    'address' => $validated['address'] ?? null,
                    'description' => $validated['description'] ?? null,
                    'price_per_night' => $validated['price_per_night'],
                    'max_guests' => $validated['max_guests'],
                    'bedrooms' => $validated['bedrooms'] ?? null,
                    'bathrooms' => $validated['bathrooms'] ?? null,
                    'status' => 'draft',
                ]);
                $this->storeImages($accommodation, $request, $paths);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($paths);
            throw $exception;
        }

        return redirect()->route('accommodations.index')
            ->with('success', 'Accommodation saved as a draft. Publish it when ready.');
    }

    public function edit(Accommodation $accommodation)
    {
        $this->assertOwned($accommodation);
        $accommodation->load('images');
        $properties = Property::where('landlord_id', $this->landlordId())
            ->orderBy('property_name')
            ->get();

        return view('accommodations.edit', compact('accommodation', 'properties'));
    }

    public function update(Request $request, Accommodation $accommodation)
    {
        $this->assertOwned($accommodation);
        $validated = $request->validate($this->rules());
        $propertyId = $this->propertyId($validated['property_id'] ?? null);
        $paths = [];

        try {
            DB::transaction(function () use ($accommodation, $validated, $propertyId, $request, &$paths) {
                $locked = Accommodation::whereKey($accommodation->id)
                    ->lockForUpdate()
                    ->firstOrFail();
                if ($locked->images()->count() + count($request->file('images', [])) > 8) {
                    throw ValidationException::withMessages([
                        'images' => 'An accommodation can have no more than eight photos.',
                    ]);
                }

                $locked->update([
                    'property_id' => $propertyId,
                    'title' => $validated['title'],
                    'place_name' => $validated['place_name'],
                    'address' => $validated['address'] ?? null,
                    'description' => $validated['description'] ?? null,
                    'price_per_night' => $validated['price_per_night'],
                    'max_guests' => $validated['max_guests'],
                    'bedrooms' => $validated['bedrooms'] ?? null,
                    'bathrooms' => $validated['bathrooms'] ?? null,
                ]);
                $this->storeImages($locked, $request, $paths);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($paths);
            throw $exception;
        }

        return redirect()->route('accommodations.index')
            ->with('success', 'Accommodation details updated.');
    }

    public function toggle(Accommodation $accommodation)
    {
        $this->assertOwned($accommodation);
        $publish = $accommodation->status !== 'published';
        $accommodation->update([
            'status' => $publish ? 'published' : 'draft',
            'published_at' => $publish ? now() : null,
        ]);

        return back()->with('success', $publish
            ? 'Accommodation published on the public homepage.'
            : 'Accommodation unpublished.');
    }

    public function destroyImage(Accommodation $accommodation, AccommodationImage $image)
    {
        $this->assertOwned($accommodation);
        abort_unless((int) $image->accommodation_id === (int) $accommodation->id, 404);
        abort_unless(
            Storage::disk('public')->delete($image->image_path),
            500,
            'Could not remove the accommodation photo.'
        );
        $image->delete();

        return back()->with('success', 'Accommodation photo removed.');
    }

    public function bookings()
    {
        $bookings = AccommodationBooking::whereHas('accommodation', fn ($query) => $query
            ->where('landlord_id', $this->landlordId()))
            ->with('accommodation')
            ->latest()
            ->paginate(20);

        return view('accommodations.bookings', compact('bookings'));
    }

    public function updateBooking(Request $request, AccommodationBooking $booking)
    {
        abort_unless(
            $booking->accommodation()
                ->where('landlord_id', $this->landlordId())
                ->exists(),
            404
        );
        $validated = $request->validate([
            'status' => ['required', 'in:confirmed,declined'],
        ]);

        DB::transaction(function () use ($booking, $validated) {
            Accommodation::whereKey($booking->accommodation_id)
                ->lockForUpdate()
                ->firstOrFail();
            $locked = AccommodationBooking::with('accommodation')
                ->lockForUpdate()
                ->findOrFail($booking->id);

            if ($validated['status'] === 'confirmed') {
                $conflict = AccommodationBooking::where('accommodation_id', $locked->accommodation_id)
                    ->where('status', 'confirmed')
                    ->where('id', '<>', $locked->id)
                    ->where('check_in', '<', $locked->check_out)
                    ->where('check_out', '>', $locked->check_in)
                    ->exists();
                if ($conflict) {
                    throw ValidationException::withMessages([
                        'booking' => 'These dates overlap another confirmed booking.',
                    ]);
                }
            }

            $locked->update([
                'status' => $validated['status'],
                'responded_at' => now(),
            ]);
        });

        return back()->with('success', 'Booking request updated.');
    }

    private function rules(): array
    {
        return [
            'property_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'place_name' => ['required', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'price_per_night' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999999999.99'],
            'max_guests' => ['required', 'integer', 'between:1,100'],
            'bedrooms' => ['nullable', 'integer', 'between:0,50'],
            'bathrooms' => ['nullable', 'integer', 'between:0,50'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    private function landlordId(): int
    {
        $id = Landlord::where('user_id', Auth::id())->value('id');
        abort_unless($id, 403, 'Landlord account not found.');

        return (int) $id;
    }

    private function assertOwned(Accommodation $accommodation): void
    {
        abort_unless((int) $accommodation->landlord_id === $this->landlordId(), 404);
    }

    private function propertyId($propertyId): ?int
    {
        if ($propertyId === null || $propertyId === '') {
            return null;
        }

        $property = Property::where('landlord_id', $this->landlordId())
            ->findOrFail($propertyId);

        return (int) $property->id;
    }

    private function storeImages(
        Accommodation $accommodation,
        Request $request,
        array &$paths
    ): void {
        $sortOrder = (int) $accommodation->images()->max('sort_order') + 1;
        foreach ($request->file('images', []) as $image) {
            $path = $image->storePublicly('accommodations', 'public');
            if (! $path) {
                throw new RuntimeException('Failed to store an accommodation photo.');
            }
            $paths[] = $path;
            $accommodation->images()->create([
                'image_path' => $path,
                'sort_order' => $sortOrder++,
            ]);
        }
    }
}
