<?php

namespace App\Http\Controllers;

use App\Models\Landlord;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Tenancy;
use App\Models\Unit;
use App\Services\TenantLimitService;
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
            ->where('landlord_id', $landlordId);

        if ($request->filled('status') &&
            in_array($request->status, ['active', 'upcoming', 'ended'], true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);

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
            ->route('tenancies.show', $tenancy)
            ->with('success', 'Tenancy ended successfully. The tenancy history has been preserved.');
    }
}
