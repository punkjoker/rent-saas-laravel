<?php

namespace App\Http\Controllers;

use App\Models\Landlord;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Tenancy;
use App\Models\Unit;
use App\Services\TenancyReconciliationCalculator;
use App\Services\TenantLimitService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TenancyController extends Controller
{
    public function __construct(
        private TenantLimitService $tenantLimitService
    ) {
    }

    private function landlordId(): int
    {
        $id = Landlord::where('user_id', Auth::id())->value('id');

        if (!$id) {
            abort(403, 'Landlord profile not found.');
        }

        return (int) $id;
    }

    private function ownedTenancy(int $id): Tenancy
    {
        return Tenancy::with(['tenant', 'property', 'unit'])
            ->where('landlord_id', $this->landlordId())
            ->findOrFail($id);
    }

    /**
     * Refresh tenancy statuses when a page is opened.
     * Upcoming agreements become active on their start date.
     * Active agreements become ended after their lease end date.
     */
    private function synchronizeStatuses(int $landlordId): void
    {
        DB::transaction(function () use ($landlordId) {
            $today = now()->toDateString();

            $starting = Tenancy::where('landlord_id', $landlordId)
                ->where('status', 'upcoming')
                ->whereDate('start_date', '<=', $today)
                ->get();

            foreach ($starting as $tenancy) {
                $tenancy->update(['status' => 'active']);

                $tenancy->unit()->update(['status' => 'occupied']);
            }

            $ending = Tenancy::where('landlord_id', $landlordId)
                ->where('status', 'active')
                ->whereDate('lease_end_date', '<', $today)
                ->get();

            foreach ($ending as $tenancy) {
                $tenancy->update([
                    'status' => 'ended',
                    'ended_at' => $tenancy->lease_end_date,
                ]);

                $this->refreshUnitStatus($tenancy->unit_id);
            }
        });
    }

    private function refreshUnitStatus(int $unitId): void
    {
        $hasActiveTenancy = Tenancy::where('unit_id', $unitId)
            ->where('status', 'active')
            ->exists();

        if (!$hasActiveTenancy) {
            Unit::where('id', $unitId)
                ->where('status', 'occupied')
                ->update(['status' => 'vacant']);
        }
    }

    /**
     * Check the tenant limit across the landlord's properties.
     * Upcoming tenancies reserve tenant capacity too.
     */
    private function enforceTenantLimit(
        int $landlordId,
        int $tenantId
    ): void {
        $this->tenantLimitService->assertCanAddTenant(
            Landlord::findOrFail($landlordId),
            $tenantId
        );
    }

    public function index(Request $request)
    {
        $landlordId = $this->landlordId();

        $this->synchronizeStatuses($landlordId);

        $query = Tenancy::with(['tenant', 'property', 'unit'])
            ->where('landlord_id', $landlordId)
            ->whereIn('status', ['active', 'upcoming']);

        if ($request->filled('status') &&
            in_array($request->status, ['active', 'upcoming'], true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $this->applySearch($query, trim($request->search));
        }

        $tenancies = $query->latest('start_date')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'active' => Tenancy::where('landlord_id', $landlordId)
                ->where('status', 'active')->count(),

            'upcoming' => Tenancy::where('landlord_id', $landlordId)
                ->where('status', 'upcoming')->count(),

            'ended' => Tenancy::where('landlord_id', $landlordId)
                ->where('status', 'ended')->count(),
        ];

        return view('tenancies.index', compact('tenancies', 'stats'));
    }

    public function vacated(Request $request)
    {
        $landlordId = $this->landlordId();

        $this->synchronizeStatuses($landlordId);

        $query = Tenancy::with(['tenant', 'property', 'unit'])
            ->where('landlord_id', $landlordId)
            ->where('status', 'ended');

        if ($request->filled('search')) {
            $this->applySearch($query, trim($request->search));
        }

        $tenancies = $query->latest('ended_at')
            ->paginate(15)
            ->withQueryString();

        return view('tenancies.vacated', compact('tenancies'));
    }

    private function applySearch(Builder $query, string $search): void
    {
        $query->where(function ($q) use ($search) {
            $q->whereHas('tenant', function ($tenantQuery) use ($search) {
                $tenantQuery->where('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->orWhereHas('property', function ($propertyQuery) use ($search) {
                $propertyQuery->where('property_name', 'like', "%{$search}%");
            })
            ->orWhereHas('unit', function ($unitQuery) use ($search) {
                $unitQuery->where('unit_number', 'like', "%{$search}%");
            });
        });
    }

    public function create()
    {
        $landlordId = $this->landlordId();

        $this->synchronizeStatuses($landlordId);

        $tenants = Tenant::where('landlord_id', $landlordId)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $properties = Property::where('landlord_id', $landlordId)
            ->where('is_active', true)
            ->with(['units' => function ($query) {
                $query->where('status', 'vacant')
                    ->orderBy('unit_number');
            }])
            ->orderBy('property_name')
            ->get();

        // Exclude units already reserved by upcoming or active tenancies.
        foreach ($properties as $property) {
            $reservedUnitIds = Tenancy::where('landlord_id', $landlordId)
                ->where('property_id', $property->id)
                ->whereIn('status', ['active', 'upcoming'])
                ->pluck('unit_id')
                ->all();

            $property->setRelation(
                'units',
                $property->units->whereNotIn('id', $reservedUnitIds)->values()
            );
        }

        return view('tenancies.create', compact('tenants', 'properties'));
    }

    public function store(Request $request)
    {
        $landlordId = $this->landlordId();

        $validated = $request->validate([
            'tenant_id' => ['required', 'integer', 'exists:tenants,id'],
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'unit_id' => ['required', 'integer', 'exists:units,id'],

            'monthly_rent' => ['required', 'numeric', 'min:0'],
            'deposit_amount' => ['required', 'numeric', 'min:0'],
            'garbage_fee' => ['required', 'numeric', 'min:0'],

            'start_date' => ['required', 'date'],
            'lease_end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'rent_due_day' => ['required', 'integer', 'between:1,28'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $tenancy = DB::transaction(function () use (
            $validated,
            $landlordId
        ) {
            // Lock the unit to prevent simultaneous allocations.
            $unit = Unit::where('id', $validated['unit_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $property = Property::where('id', $validated['property_id'])
                ->where('landlord_id', $landlordId)
                ->firstOrFail();

            if ($unit->property_id !== $property->id) {
                throw ValidationException::withMessages([
                    'unit_id' => 'The selected unit does not belong to this property.',
                ]);
            }

            Tenant::where('id', $validated['tenant_id'])
                ->where('landlord_id', $landlordId)
                ->firstOrFail();

            if (!$property->is_active) {
                throw ValidationException::withMessages([
                    'property_id' => 'This property is inactive and cannot receive new tenancies.',
                ]);
            }

            if ($unit->status !== 'vacant') {
                throw ValidationException::withMessages([
                    'unit_id' => 'This unit is not vacant.',
                ]);
            }

            $overlapping = Tenancy::where('unit_id', $unit->id)
                ->whereIn('status', ['active', 'upcoming'])
                ->whereDate(
                    'start_date',
                    '<=',
                    $validated['lease_end_date'] ?? '9999-12-31'
                )
                ->where(function ($query) use ($validated) {
                    $query->whereNull('lease_end_date')
                        ->orWhereDate(
                            'lease_end_date',
                            '>=',
                            $validated['start_date']
                        );
                })
                ->exists();

            if ($overlapping) {
                throw ValidationException::withMessages([
                    'unit_id' => 'This unit already has a tenancy covering the selected dates.',
                ]);
            }

            $this->enforceTenantLimit(
                $landlordId,
                (int) $validated['tenant_id']
            );

            $status = $validated['start_date'] > now()->toDateString()
                ? 'upcoming'
                : 'active';

            $tenancy = Tenancy::create([
                'landlord_id' => $landlordId,
                'tenant_id' => $validated['tenant_id'],
                'property_id' => $property->id,
                'unit_id' => $unit->id,
                'monthly_rent' => $validated['monthly_rent'],
                'deposit_amount' => $validated['deposit_amount'],
                'garbage_fee' => $validated['garbage_fee'],
                'start_date' => $validated['start_date'],
                'lease_end_date' => $validated['lease_end_date'],
                'rent_due_day' => $validated['rent_due_day'],
                'status' => $status,
                'notes' => $validated['notes'] ?? null,
            ]);

            if ($status === 'active') {
                $unit->update(['status' => 'occupied']);
            }

            return $tenancy;
        });

        return redirect()
            ->route('tenancies.show', $tenancy->id)
            ->with('success', 'Tenancy created and unit allocated successfully.');
    }

    public function show(int $tenancy)
    {
        $landlordId = $this->landlordId();

        $this->synchronizeStatuses($landlordId);

        $tenancy = $this->ownedTenancy($tenancy);

        return view('tenancies.show', compact('tenancy'));
    }

    public function end(Request $request, int $tenancy)
    {
        $landlordId = $this->landlordId();

        $validated = $request->validate([
            'ended_at' => ['required', 'date', 'before_or_equal:today'],
        ]);

        DB::transaction(function () use ($tenancy, $landlordId, $validated) {
            $record = Tenancy::where('landlord_id', $landlordId)
                ->lockForUpdate()
                ->findOrFail($tenancy);

            if ($record->status === 'ended') {
                throw ValidationException::withMessages([
                    'ended_at' => 'This tenancy has already ended.',
                ]);
            }

            $record->update([
                'status' => 'ended',
                'ended_at' => $validated['ended_at'],
            ]);

            $this->refreshUnitStatus($record->unit_id);
        });

        return redirect()
            ->route('tenancies.vacated')
            ->with('success', 'Tenancy vacated successfully. Its history is available in the Vacated Tenancies section.');
    }

    public function reconciliation(
        int $tenancy,
        TenancyReconciliationCalculator $calculator
    ) {
        $record = $this->ownedTenancy($tenancy);
        abort_unless($record->status === 'ended', 404);

        return view(
            'tenancies.reconcile',
            $this->reconciliationDetails($record, $calculator)
        );
    }

    public function saveReconciliation(Request $request, int $tenancy)
    {
        $record = $this->ownedTenancy($tenancy);
        abort_unless($record->status === 'ended', 404);

        $validated = $request->validate([
            'damages' => ['nullable', 'array', 'max:50'],
            'damages.*.description' => ['required', 'string', 'max:255'],
            'damages.*.amount' => [
                'required',
                'numeric',
                'decimal:0,2',
                'gt:0',
                'max:9999999999.99',
            ],
        ]);

        DB::transaction(function () use ($record, $validated) {
            $lockedTenancy = Tenancy::where('landlord_id', $record->landlord_id)
                ->lockForUpdate()
                ->findOrFail($record->id);
            $reconciliation = $lockedTenancy->reconciliation()->firstOrCreate([]);

            $reconciliation->damages()->delete();
            $reconciliation->damages()->createMany($validated['damages'] ?? []);
        });

        return redirect()
            ->route('tenancies.reconciliation', $record->id)
            ->with('success', 'Reconciliation saved. The settlement balance has been updated.');
    }

    public function finalizeReconciliation(int $tenancy)
    {
        $record = $this->ownedTenancy($tenancy);
        abort_unless($record->status === 'ended', 404);

        DB::transaction(function () use ($record) {
            $lockedTenancy = Tenancy::where('landlord_id', $record->landlord_id)
                ->lockForUpdate()
                ->findOrFail($record->id);
            $reconciliation = $lockedTenancy->reconciliation()->firstOrCreate([]);

            if (!$reconciliation->finalized_at) {
                $reconciliation->update(['finalized_at' => now()]);
            }
        });

        return redirect()
            ->route('tenancies.reconciliation.final', $record->id)
            ->with('success', 'Reconciliation marked as final. You can still edit it later.');
    }

    public function finalReconciliation(
        int $tenancy,
        TenancyReconciliationCalculator $calculator
    ) {
        $record = $this->ownedTenancy($tenancy);
        abort_unless($record->status === 'ended', 404);

        return view(
            'tenancies.final',
            $this->reconciliationDetails($record, $calculator)
        );
    }

    private function reconciliationDetails(
        Tenancy $tenancy,
        TenancyReconciliationCalculator $calculator
    ): array {
        $asOfDate = Carbon::parse(
            $tenancy->ended_at ?? $tenancy->lease_end_date ?? now()->toDateString()
        );
        $charges = $tenancy->rentCharges()
            ->where('month_year', '<=', $asOfDate->format('Y-m'))
            ->orderBy('month_year')
            ->get();
        $payments = $tenancy->rentPayments()
            ->where('paid_at', '<=', $asOfDate->copy()->endOfDay())
            ->orderBy('paid_at')
            ->orderBy('id')
            ->get();
        $depositPayments = $tenancy->depositPayments()
            ->where('paid_at', '<=', $asOfDate->copy()->endOfDay())
            ->orderBy('paid_at')
            ->orderBy('id')
            ->get();
        $reconciliation = $tenancy->reconciliation()
            ->with('damages')
            ->first();
        $damages = $reconciliation?->damages ?? collect();
        $summary = $calculator->calculate(
            $charges,
            $payments,
            $depositPayments,
            $damages
        );

        return compact(
            'tenancy',
            'asOfDate',
            'charges',
            'payments',
            'depositPayments',
            'reconciliation',
            'damages',
            'summary'
        );
    }
}
