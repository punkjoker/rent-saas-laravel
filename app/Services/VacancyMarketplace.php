<?php

namespace App\Services;

use App\Models\VacancyListing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class VacancyMarketplace
{
    public function cards(
        ?string $place = null,
        ?float $minimumPrice = null,
        ?float $maximumPrice = null
    ): Collection {
        $listings = VacancyListing::query()
            ->where('status', 'published')
            ->where(function (Builder $query) {
                $query->where('source', 'admin')
                    ->orWhere(function (Builder $landlordQuery) {
                        $landlordQuery->where('source', 'landlord')
                            ->whereHas('unit', function (Builder $unitQuery) {
                                $unitQuery->where('status', 'vacant')
                                    ->whereDoesntHave('tenancies', fn (Builder $tenancyQuery) => $tenancyQuery
                                        ->whereIn('status', ['active', 'upcoming']));
                            })
                            ->whereHas('property', function (Builder $propertyQuery) {
                                $propertyQuery->where('is_active', true)
                                    ->whereNull('admin_revoked_at')
                                    ->whereHas('landlord', fn (Builder $landlordQuery) => $landlordQuery
                                        ->where('is_active', true)
                                        ->where('approval_status', 'approved'));
                            });
                    });
            })
            ->when($place !== null && $place !== '', function (Builder $query) use ($place) {
                $query->where(function (Builder $placeQuery) use ($place) {
                    $placeQuery->where(function (Builder $adminQuery) use ($place) {
                        $adminQuery->where('source', 'admin')
                            ->where(function (Builder $locationQuery) use ($place) {
                                $locationQuery->where('place_name', 'like', "%{$place}%")
                                    ->orWhere('address', 'like', "%{$place}%");
                            });
                    })->orWhere(function (Builder $landlordQuery) use ($place) {
                        $landlordQuery->where('source', 'landlord')
                            ->whereHas('property', function (Builder $propertyQuery) use ($place) {
                                $propertyQuery->where('town', 'like', "%{$place}%")
                                    ->orWhere('county', 'like', "%{$place}%")
                                    ->orWhere('address', 'like', "%{$place}%");
                            });
                    });
                });
            })
            ->when($minimumPrice !== null, fn (Builder $query) => $query
                ->where('monthly_rent', '>=', $minimumPrice))
            ->when($maximumPrice !== null, fn (Builder $query) => $query
                ->where('monthly_rent', '<=', $maximumPrice))
            ->with([
                'images',
                'unit',
                'property.listing' => fn ($query) => $query
                    ->where('status', 'approved'),
                'property.listing.images',
            ])
            ->orderBy('place_name')
            ->get();

        $cards = $listings
            ->filter(fn (VacancyListing $listing) => $listing->source === 'admin')
            ->map(fn (VacancyListing $listing) => $this->makeCard(
                collect([$listing]),
                $listing->title,
                $listing->place_name,
                $listing->unit_type
            ));

        $landlordGroups = $listings
            ->filter(fn (VacancyListing $listing) => $listing->source === 'landlord')
            ->groupBy(function (VacancyListing $listing) {
                return $listing->property_id.'|'.Str::lower(
                    $this->normalizedUnitType($listing->unit_type)
                );
            });

        foreach ($landlordGroups as $group) {
            $first = $group->first();
            $property = $first->property;
            $unitType = $this->normalizedUnitType($first->unit_type);
            $location = collect([
                $property?->town,
                $property?->county,
            ])->filter()->unique()->join(', ');

            $cards->push($this->makeCard(
                $group,
                trim(($property?->property_name ?? 'Rental property').' · '.$unitType),
                $location ?: ($property?->address ?? ''),
                $unitType
            ));
        }

        return $cards->sortBy(fn (array $card) => Str::lower(
            $card['place'].' '.$card['title']
        ))->values();
    }

    public function detail(int $id): array
    {
        $listing = VacancyListing::with([
            'images',
            'unit',
            'property.landlord',
            'property.listing' => fn ($query) => $query
                ->where('status', 'approved'),
            'property.listing.images',
        ])->whereKey($id)
            ->where('status', 'published')
            ->firstOrFail();

        if ($listing->source === 'landlord') {
            abort_unless(
                $listing->unit &&
                $listing->unit->status === 'vacant' &&
                ! $listing->unit->tenancies()
                    ->whereIn('status', ['active', 'upcoming'])
                    ->exists() &&
                $listing->property &&
                $listing->property->is_active &&
                ! $listing->property->admin_revoked_at &&
                $listing->property->landlord &&
                $listing->property->landlord->is_active &&
                $listing->property->landlord->approval_status === 'approved',
                404
            );
        }

        abort_unless(in_array($listing->source, ['admin', 'landlord'], true), 404);

        if ($listing->source === 'landlord') {
            $availableUnits = VacancyListing::where('source', 'landlord')
                ->where('property_id', $listing->property_id)
                ->whereRaw(
                    "LOWER(COALESCE(NULLIF(TRIM(unit_type), ''), 'Other')) = ?",
                    [Str::lower($this->normalizedUnitType($listing->unit_type))]
                )
                ->where('status', 'published')
                ->whereHas('unit', fn (Builder $query) => $query
                    ->where('status', 'vacant')
                    ->whereDoesntHave('tenancies', fn (Builder $tenancyQuery) => $tenancyQuery
                        ->whereIn('status', ['active', 'upcoming'])))
                ->count();
        } else {
            $availableUnits = 1;
        }

        return compact('listing', 'availableUnits');
    }

    private function makeCard(
        Collection $listings,
        string $title,
        string $place,
        ?string $unitType
    ): array {
        $first = $listings->first();
        $propertyImage = $first->source === 'landlord'
            ? $first->property?->listing?->images?->first()?->image_path
            : null;

        return [
            'id' => $first->id,
            'title' => $title,
            'place' => $place,
            'unit_type' => $unitType,
            'count' => $listings->count(),
            'rent_min' => (float) $listings->min('monthly_rent'),
            'rent_max' => (float) $listings->max('monthly_rent'),
            'image' => $first->images->first()?->image_path ?? $propertyImage,
            'source' => $first->source,
            'address' => $first->address ?? $first->property?->address,
            'bedrooms' => $first->bedrooms,
            'bathrooms' => $first->bathrooms,
        ];
    }

    private function normalizedUnitType(?string $unitType): string
    {
        $unitType = trim((string) $unitType);

        return $unitType === '' ? 'Other' : $unitType;
    }
}
