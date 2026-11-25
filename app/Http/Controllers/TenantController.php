<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Landlord;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Tenancy;
use App\Models\Unit;
use App\Services\TenantLimitService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TenantController extends Controller
{
    public function __construct(
        private TenantLimitService $tenantLimitService
    ) {
    }

    private function landlordId(): int
    {
        return (int) Landlord::where('user_id', Auth::id())
            ->value('id') ?: abort(403, 'Landlord profile not found.');
    }

    private function ownedTenant(int $tenantId): Tenant
    {
        return Tenant::where('id', $tenantId)
            ->where('landlord_id', $this->landlordId())
            ->firstOrFail();
    }

    private function visibleTenantQuery(int $landlordId): Builder
    {
        return Tenant::where('landlord_id', $landlordId)
            ->where(function (Builder $query) {
                $query->whereDoesntHave('tenancies')
                    ->orWhereHas('tenancies', function (Builder $tenancyQuery) {
                        $tenancyQuery->whereIn('status', ['active', 'upcoming']);
                    });
            });
    }

    public function index(Request $request)
    {
        $landlordId = $this->landlordId();

        $query = $this->visibleTenantQuery($landlordId);

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('alternative_phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('identification_number', 'like', "%{$search}%");
            });
        }

        $tenants = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => $this->visibleTenantQuery($landlordId)->count(),
            'registered_this_month' => $this->visibleTenantQuery($landlordId)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return view('tenants.index', compact('tenants', 'stats'));
    }

    public function create()
    {
        $landlordId = $this->landlordId();

        $properties = Property::where('landlord_id', $landlordId)
            ->where('is_active', true)
            ->with(['units' => function ($query) {
                $query->where('status', 'vacant')
                    ->orderBy('unit_number');
            }])
            ->orderBy('property_name')
            ->get();

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

        $propertyOptions = $properties
            ->filter(function ($property) {
                return $property->units->isNotEmpty();
            })
            ->map(function ($property) {
                return [
                    'id' => $property->id,
                    'name' => $property->property_name,
                    'units' => $property->units->map(function ($unit) {
                        return [
                            'id' => $unit->id,
                            'number' => $unit->unit_number,
                            'rent' => $unit->monthly_rent,
                            'deposit' => $unit->deposit_amount,
                            'garbage_fee' => $unit->garbage_fee,
                        ];
                    })->values(),
                ];
            })->values();

        return view('tenants.create', compact('propertyOptions'));
    }

    public function store(Request $request)
    {
        $landlordId = $this->landlordId();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],

            'identification_type' => [
                'nullable',
                Rule::in(['national_id', 'passport']),
            ],
            'identification_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('tenants', 'identification_number')
                    ->where('landlord_id', $landlordId),
                'required_with:identification_type',
            ],

            'phone' => ['required', 'string', 'max:30'],
            'alternative_phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],

            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'occupation' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:2000'],

            'emergency_contact_name' => ['nullable', 'string', 'max:150'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],

            'notes' => ['nullable', 'string', 'max:5000'],

            'property_id' => [
                'nullable',
                'integer',
                'exists:properties,id',
                'required_with:unit_id',
            ],
            'unit_id' => [
                'nullable',
                'integer',
                'exists:units,id',
                'required_with:property_id',
            ],
        ]);

        $hasAssignment = !empty($validated['property_id'])
            && !empty($validated['unit_id']);

        $tenant = DB::transaction(function () use (
            $validated,
            $landlordId,
            $hasAssignment
        ) {
            $property = null;
            $unit = null;

            if ($hasAssignment) {
                $property = Property::where('landlord_id', $landlordId)
                    ->findOrFail($validated['property_id']);

                if (!$property->is_active) {
                    throw ValidationException::withMessages([
                        'property_id' => 'This property is inactive and cannot receive new tenancies.',
                    ]);
                }

                $unit = Unit::where('property_id', $property->id)
                    ->lockForUpdate()
                    ->findOrFail($validated['unit_id']);

                if ($unit->status !== 'vacant') {
                    throw ValidationException::withMessages([
                        'unit_id' => 'This unit is no longer vacant.',
                    ]);
                }

                $today = now()->toDateString();
                $hasReservation = Tenancy::where('unit_id', $unit->id)
                    ->whereIn('status', ['active', 'upcoming'])
                    ->where(function ($query) use ($today) {
                        $query->whereNull('lease_end_date')
                            ->orWhereDate('lease_end_date', '>=', $today);
                    })
                    ->exists();

                if ($hasReservation) {
                    throw ValidationException::withMessages([
                        'unit_id' => 'This unit already has an active or scheduled tenancy.',
                    ]);
                }

                $this->tenantLimitService->assertCanAddTenant(
                    Landlord::findOrFail($landlordId)
                );
            }

            unset($validated['property_id'], $validated['unit_id']);
            $validated['landlord_id'] = $landlordId;
            $tenant = Tenant::create($validated);

            if ($unit && $property) {
                Tenancy::create([
                    'landlord_id' => $landlordId,
                    'tenant_id' => $tenant->id,
                    'property_id' => $property->id,
                    'unit_id' => $unit->id,
                    'monthly_rent' => $unit->monthly_rent,
                    'deposit_amount' => $unit->deposit_amount,
                    'garbage_fee' => $unit->garbage_fee,
                    'start_date' => now()->toDateString(),
                    'lease_end_date' => null,
                    'rent_due_day' => 1,
                    'status' => 'active',
                ]);

                $unit->vacancyListing()->where('status', 'published')->update([
                    'status' => 'unpublished',
                    'published_at' => null,
                ]);
                $unit->update(['status' => 'occupied']);
            }

            return $tenant;
        });

        return redirect()
            ->route('tenants.show', $tenant->id)
            ->with(
                'success',
                $hasAssignment
                    ? 'Tenant registered and assigned to the selected unit.'
                    : 'Tenant registered successfully.'
            );
    }

    public function show(int $tenant)
    {
        $tenant = $this->ownedTenant($tenant);

        return view('tenants.show', compact('tenant'));
    }

    public function edit(int $tenant)
    {
        $tenant = $this->ownedTenant($tenant);

        return view('tenants.edit', compact('tenant'));
    }

    public function update(Request $request, int $tenant)
    {
        $tenant = $this->ownedTenant($tenant);

        $landlordId = $this->landlordId();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],

            'identification_type' => [
                'nullable',
                Rule::in(['national_id', 'passport']),
            ],
            'identification_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('tenants', 'identification_number')
                    ->where('landlord_id', $landlordId)
                    ->ignore($tenant->id),
                'required_with:identification_type',
            ],

            'phone' => ['required', 'string', 'max:30'],
            'alternative_phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],

            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'occupation' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:2000'],

            'emergency_contact_name' => ['nullable', 'string', 'max:150'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],

            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $tenant->update($validated);

        return redirect()
            ->route('tenants.show', $tenant->id)
            ->with('success', 'Tenant details updated successfully.');
    }
}
