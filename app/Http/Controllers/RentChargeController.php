<?php

namespace App\Http\Controllers;

use App\Models\Landlord;
use App\Models\Property;
use App\Models\RentCharge;
use App\Models\Tenancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RentChargeController extends Controller
{
    /**
     * Display the monthly rent charge register.
     */
    public function index(Request $request)
    {
        $landlordId = Landlord::where('user_id', Auth::id())->value('id');

        abort_unless($landlordId, 403, 'Landlord account not found.');

        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'property_id' => ['nullable', 'integer'],
        ]);

        $month = $validated['month'] ?? now()->format('Y-m');
        $propertyId = $validated['property_id'] ?? null;

        $monthStart = $month . '-01';
        $monthEnd = date('Y-m-t', strtotime($monthStart));

        $properties = Property::where('landlord_id', $landlordId)
            ->orderBy('property_name')
            ->get();

        if ($propertyId) {
            $propertyExists = $properties->contains(
                fn ($property) => (int) $property->id === (int) $propertyId
            );

            abort_unless($propertyExists, 404, 'Property not found.');
        }

        $tenanciesQuery = Tenancy::where('landlord_id', $landlordId)
            ->whereDate('start_date', '<=', $monthEnd)
            ->where(function ($query) use ($monthStart) {
                // If ended_at is set, it is the authoritative tenancy end date.
                $query->whereDate('ended_at', '>=', $monthStart)
                    ->orWhere(function ($query) use ($monthStart) {
                        $query->whereNull('ended_at')
                            ->where(function ($query) use ($monthStart) {
                                $query->whereNull('lease_end_date')
                                    ->orWhereDate('lease_end_date', '>=', $monthStart);
                            });
                    });
            })
            ->with([
                'tenant',
                'property',
                'unit',
                'rentCharges' => function ($query) use ($month) {
                    $query->where('month_year', $month);
                },
            ]);

        if ($propertyId) {
            $tenanciesQuery->where('property_id', $propertyId);
        }

        $tenancies = $tenanciesQuery->get()
            ->sort(function ($a, $b) {
                $propertyComparison = strcasecmp(
                    $a->property->property_name ?? '',
                    $b->property->property_name ?? ''
                );

                if ($propertyComparison !== 0) {
                    return $propertyComparison;
                }

                return strnatcasecmp(
                    $a->unit->unit_number ?? '',
                    $b->unit->unit_number ?? ''
                );
            })
            ->values();

        return view('rent-charges.index', compact(
            'tenancies',
            'properties',
            'month',
            'propertyId'
        ));
    }

    /**
     * Save or update the monthly charges.
     */
    public function store(Request $request)
    {
        $landlordId = Landlord::where('user_id', Auth::id())->value('id');

        abort_unless($landlordId, 403, 'Landlord account not found.');

        $validated = $request->validate([
            'month_year' => ['required', 'date_format:Y-m'],
            'property_id' => ['nullable', 'integer'],
            'charges' => ['required', 'array', 'min:1'],
            'charges.*.tenancy_id' => [
                'required',
                'integer',
                'distinct',
            ],
            'charges.*.rent_amount' => [
                'required',
                'numeric',
                'min:0',
            ],
            'charges.*.garbage_fee' => [
                'required',
                'numeric',
                'min:0',
            ],
            'charges.*.water_bill' => [
                'required',
                'numeric',
                'min:0',
            ],
        ]);

        $month = $validated['month_year'];
        $propertyId = $validated['property_id'] ?? null;

        if ($propertyId) {
            $propertyBelongsToLandlord = Property::where('id', $propertyId)
                ->where('landlord_id', $landlordId)
                ->exists();

            abort_unless($propertyBelongsToLandlord, 404, 'Property not found.');
        }

        $monthStart = $month . '-01';
        $monthEnd = date('Y-m-t', strtotime($monthStart));

        $submittedIds = collect($validated['charges'])
            ->pluck('tenancy_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        // Recheck ownership and month overlap on the server.
        $eligibleTenanciesQuery = Tenancy::where('landlord_id', $landlordId)
            ->whereIn('id', $submittedIds)
            ->whereDate('start_date', '<=', $monthEnd)
            ->where(function ($query) use ($monthStart) {
                $query->whereDate('ended_at', '>=', $monthStart)
                    ->orWhere(function ($query) use ($monthStart) {
                        $query->whereNull('ended_at')
                            ->where(function ($query) use ($monthStart) {
                                $query->whereNull('lease_end_date')
                                    ->orWhereDate('lease_end_date', '>=', $monthStart);
                            });
                    });
            });

        if ($propertyId) {
            $eligibleTenanciesQuery->where('property_id', $propertyId);
        }

        $eligibleIds = $eligibleTenanciesQuery->pluck('id')
            ->map(fn ($id) => (int) $id);

        if ($eligibleIds->count() !== $submittedIds->count()) {
            throw ValidationException::withMessages([
                'charges' => 'One or more submitted tenancies do not belong to your account or do not overlap the selected month. Please refresh the register and try again.',
            ]);
        }

        DB::transaction(function () use ($validated, $month, $eligibleIds) {
            foreach ($validated['charges'] as $chargeData) {
                $tenancyId = (int) $chargeData['tenancy_id'];

                if (!$eligibleIds->contains($tenancyId)) {
                    continue;
                }

                $rent = round((float) $chargeData['rent_amount'], 2);
                $garbage = round((float) $chargeData['garbage_fee'], 2);
                $water = round((float) $chargeData['water_bill'], 2);

                $total = round($rent + $garbage + $water, 2);

                RentCharge::updateOrCreate(
                    [
                        'tenancy_id' => $tenancyId,
                        'month_year' => $month,
                    ],
                    [
                        'rent_amount' => $rent,
                        'garbage_fee' => $garbage,
                        'water_bill' => $water,
                        'total_due' => $total,
                    ]
                );
            }
        });

        return redirect()
            ->route('rent-charges.index', [
                'month' => $month,
                'property_id' => $propertyId,
            ])
            ->with('success', 'Rent charges saved successfully.');
    }
}