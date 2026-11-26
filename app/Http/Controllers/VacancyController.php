<?php

namespace App\Http\Controllers;

use App\Models\Landlord;
use App\Models\Unit;
use App\Models\VacancyListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VacancyController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'place' => ['nullable', 'string', 'max:100'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'gte:min_price'],
        ]);
        $landlordId = $this->landlordId();
        $vacancies = VacancyListing::where('source', 'landlord')
            ->whereHas('property', fn ($query) => $query
                ->where('landlord_id', $landlordId)
                ->when(! empty($filters['place']), fn ($propertyQuery) => $propertyQuery
                    ->where(function ($placeQuery) use ($filters) {
                        $placeQuery->where('town', 'like', '%'.$filters['place'].'%')
                            ->orWhere('county', 'like', '%'.$filters['place'].'%')
                            ->orWhere('address', 'like', '%'.$filters['place'].'%');
                    })))
            ->when(isset($filters['min_price']), fn ($query) => $query
                ->where('monthly_rent', '>=', $filters['min_price']))
            ->when(isset($filters['max_price']), fn ($query) => $query
                ->where('monthly_rent', '<=', $filters['max_price']))
            ->with([
                'property',
                'unit.tenancies' => fn ($query) => $query
                    ->whereIn('status', ['active', 'upcoming']),
            ])
            ->latest('updated_at')
            ->paginate(20);

        return view('vacancies.index', compact('vacancies', 'filters'));
    }

    public function toggle(Unit $unit)
    {
        $landlordId = $this->landlordId();

        DB::transaction(function () use ($unit, $landlordId) {
            $lockedUnit = Unit::whereKey($unit->id)
                ->whereHas('property', fn ($query) => $query
                    ->where('landlord_id', $landlordId))
                ->lockForUpdate()
                ->with('property.landlord')
                ->firstOrFail();

            if ($lockedUnit->status !== 'vacant' ||
                $lockedUnit->tenancies()
                    ->whereIn('status', ['active', 'upcoming'])
                    ->exists()) {
                throw ValidationException::withMessages([
                    'vacancy' => 'Only vacant units without an active or upcoming tenancy can be advertised.',
                ]);
            }

            $property = $lockedUnit->property;
            if (! $property->is_active ||
                $property->admin_revoked_at ||
                ! $property->landlord?->is_active ||
                $property->landlord?->approval_status !== 'approved') {
                throw ValidationException::withMessages([
                    'vacancy' => 'This property or landlord account is not eligible to advertise vacancies.',
                ]);
            }

            $listing = $lockedUnit->vacancyListing()->first();
            if ($listing && $listing->status === 'published') {
                $listing->update([
                    'status' => 'unpublished',
                    'published_at' => null,
                ]);

                return;
            }

            $listing ??= new VacancyListing;
            $listing->fill([
                'source' => 'landlord',
                'property_id' => $property->id,
                'unit_id' => $lockedUnit->id,
                'created_by' => Auth::id(),
                'title' => trim($property->property_name.' · '.($lockedUnit->unit_type ?: 'Unit')),
                'place_name' => $property->town ?: ($property->county ?: 'Not specified'),
                'address' => $property->address,
                'unit_type' => trim($lockedUnit->unit_type ?: '') ?: 'Other',
                'monthly_rent' => $lockedUnit->monthly_rent,
                'contact_phone' => $property->landlord->phone,
                'description' => $lockedUnit->description ?: $property->description,
                'status' => 'published',
                'published_at' => now(),
            ]);
            $listing->save();
        });

        return back()->with('success', 'Vacancy advertisement status updated.');
    }

    private function landlordId(): int
    {
        $landlordId = Landlord::where('user_id', Auth::id())->value('id');
        abort_unless($landlordId, 403, 'Landlord account not found.');

        return (int) $landlordId;
    }
}
