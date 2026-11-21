<?php

namespace App\Http\Controllers;

use App\Models\Landlord;
use App\Models\Property;
use App\Models\RentCharge;
use App\Models\Tenancy;
use App\Exports\RentChargesExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class RentChargeController extends Controller
{
    private function landlordId(): int
    {
        $landlordId = Landlord::where('user_id', Auth::id())->value('id');

        abort_unless($landlordId, 403, 'Landlord account not found.');

        return (int) $landlordId;
    }

    private function reportFilters(Request $request, int $landlordId): array
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'property_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $propertyId = isset($validated['property_id'])
            ? (int) $validated['property_id']
            : null;

        if ($propertyId !== null) {
            Property::where('landlord_id', $landlordId)
                ->findOrFail($propertyId);
        }

        return [
            'month' => $validated['month'] ?? now()->format('Y-m'),
            'propertyId' => $propertyId,
            'search' => trim($validated['search'] ?? ''),
        ];
    }

    private function applyReportFilters(
        Builder $query,
        int $landlordId,
        string $month,
        ?int $propertyId,
        string $search
    ): Builder {
        $monthStart = $month . '-01';
        $monthEnd = date('Y-m-t', strtotime($monthStart));

        $query->where('landlord_id', $landlordId)
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

        if ($propertyId !== null) {
            $query->where('property_id', $propertyId);
        }

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->whereHas('unit', function ($unitQuery) use ($search) {
                    $unitQuery->where('unit_number', 'like', "%{$search}%");
                })->orWhereHas('tenant', function ($tenantQuery) use ($search) {
                    $tenantQuery->where('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            });
        }

        return $query;
    }

    private function savedCharges(
        int $landlordId,
        string $month,
        ?int $propertyId,
        string $search
    ) {
        $charges = RentCharge::where('month_year', $month)
            ->whereHas('tenancy', function (Builder $query) use (
                $landlordId,
                $month,
                $propertyId,
                $search
            ) {
                $this->applyReportFilters(
                    $query,
                    $landlordId,
                    $month,
                    $propertyId,
                    $search
                );
            })
            ->with(['tenancy.tenant', 'tenancy.property', 'tenancy.unit'])
            ->get();

        return $charges->sort(function ($a, $b) {
            $propertyComparison = strcasecmp(
                $a->tenancy->property->property_name ?? '',
                $b->tenancy->property->property_name ?? ''
            );

            if ($propertyComparison !== 0) {
                return $propertyComparison;
            }

            return strnatcasecmp(
                $a->tenancy->unit->unit_number ?? '',
                $b->tenancy->unit->unit_number ?? ''
            );
        })->values();
    }

    private function chargeTotals(Collection $charges): array
    {
        return [
            'rent' => $charges->sum(fn ($charge) => (float) $charge->rent_amount),
            'garbage' => $charges->sum(fn ($charge) => (float) $charge->garbage_fee),
            'water' => $charges->sum(fn ($charge) => (float) $charge->water_bill),
            'total' => $charges->sum(fn ($charge) => (float) $charge->total_due),
        ];
    }

    /**
     * Display the monthly rent charge register.
     */
    public function index(Request $request)
    {
        $landlordId = $this->landlordId();
        $filters = $this->reportFilters($request, $landlordId);
        $month = $filters['month'];
        $propertyId = $filters['propertyId'];
        $search = $filters['search'];

        $properties = Property::where('landlord_id', $landlordId)
            ->orderBy('property_name')
            ->get();

        $tenanciesQuery = $this->applyReportFilters(
            Tenancy::query(),
            $landlordId,
            $month,
            $propertyId,
            $search
        )
            ->with([
                'tenant',
                'property',
                'unit',
                'rentCharges' => function ($query) use ($month) {
                    $query->where('month_year', $month);
                },
            ]);

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

        $savedCharges = $this->savedCharges(
            $landlordId,
            $month,
            $propertyId,
            $search
        );
        $chargeTotals = $this->chargeTotals($savedCharges);

        return view('rent-charges.index', compact(
            'tenancies',
            'savedCharges',
            'chargeTotals',
            'properties',
            'month',
            'propertyId',
            'search'
        ));
    }

    public function exportExcel(Request $request)
    {
        $landlordId = $this->landlordId();
        $filters = $this->reportFilters($request, $landlordId);
        $charges = $this->savedCharges(
            $landlordId,
            $filters['month'],
            $filters['propertyId'],
            $filters['search']
        );

        return Excel::download(
            new RentChargesExport($charges),
            'rent-charges-' . $filters['month'] . '.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $landlordId = $this->landlordId();
        $filters = $this->reportFilters($request, $landlordId);
        $charges = $this->savedCharges(
            $landlordId,
            $filters['month'],
            $filters['propertyId'],
            $filters['search']
        );
        $totals = $this->chargeTotals($charges);
        $property = $filters['propertyId'] !== null
            ? Property::where('landlord_id', $landlordId)
                ->findOrFail($filters['propertyId'])
            : null;

        return Pdf::loadView('rent-charges.pdf', [
            'charges' => $charges,
            'totals' => $totals,
            'month' => $filters['month'],
            'property' => $property,
        ])->setPaper('a4', 'landscape')
            ->download('rent-charges-' . $filters['month'] . '.pdf');
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
            'search' => ['nullable', 'string', 'max:100'],
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
                'search' => trim($validated['search'] ?? ''),
            ])
            ->with('success', 'Rent charges saved successfully.');
    }
}