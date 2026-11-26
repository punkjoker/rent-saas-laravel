<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Landlord;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PropertyController extends Controller
{
    private function landlordId(): int
    {
        return (int) Landlord::where('user_id', Auth::id())
            ->value('id') ?: abort(403, 'Landlord profile not found.');
    }

    public function index(Request $request)
    {
        $landlordId = $this->landlordId();

        $query = Property::where('landlord_id', $landlordId)
            ->withCount('units')
            ->withCount([
                'units as occupied_units_count' => function ($q) {
                    $q->where('status', 'occupied');
                },
                'units as vacant_units_count' => function ($q) {
                    $q->where('status', 'vacant');
                },
                'units as maintenance_units_count' => function ($q) {
                    $q->where('status', 'maintenance');
                },
            ]);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'place' => ['nullable', 'string', 'max:100'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'gte:min_price'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('property_name', 'like', "%{$search}%")
                    ->orWhere('property_code', 'like', "%{$search}%")
                    ->orWhere('town', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['place'])) {
            $place = $filters['place'];
            $query->where(function ($placeQuery) use ($place) {
                $placeQuery->where('town', 'like', "%{$place}%")
                    ->orWhere('county', 'like', "%{$place}%")
                    ->orWhere('address', 'like', "%{$place}%");
            });
        }

        if (isset($filters['min_price']) || isset($filters['max_price'])) {
            $query->whereHas('units', function ($unitQuery) use ($filters) {
                $unitQuery
                    ->when(isset($filters['min_price']), fn ($q) => $q
                        ->where('monthly_rent', '>=', $filters['min_price']))
                    ->when(isset($filters['max_price']), fn ($q) => $q
                        ->where('monthly_rent', '<=', $filters['max_price']));
            });
        }

        if (! empty($filters['status'])) {
            $query->where(
                'is_active',
                $filters['status'] === 'active'
            );
        }

        $properties = $query->latest()->paginate(10)->withQueryString();

        $stats = [
            'properties' => Property::where('landlord_id', $landlordId)->count(),
            'units' => DB::table('units')
                ->join('properties', 'units.property_id', '=', 'properties.id')
                ->where('properties.landlord_id', $landlordId)
                ->count(),
            'occupied' => DB::table('units')
                ->join('properties', 'units.property_id', '=', 'properties.id')
                ->where('properties.landlord_id', $landlordId)
                ->where('units.status', 'occupied')
                ->count(),
            'vacant' => DB::table('units')
                ->join('properties', 'units.property_id', '=', 'properties.id')
                ->where('properties.landlord_id', $landlordId)
                ->where('units.status', 'vacant')
                ->count(),
        ];

        return view('properties.index', compact('properties', 'stats'));
    }

    public function create()
    {
        return view('properties.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_name' => ['required', 'string', 'max:255'],
            'property_code' => [
                'nullable',
                'string',
                'max:255',
                'unique:properties,property_code',
            ],
            'property_type' => ['required', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'town' => ['nullable', 'string', 'max:255'],
            'county' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'unit_prefix' => ['required', 'alpha_dash', 'max:20'],
            'units_count' => ['required', 'integer', 'min:1', 'max:500'],
            'default_rent' => ['required', 'numeric', 'min:0'],
            'default_deposit' => ['required', 'numeric', 'min:0'],
            'default_garbage_fee' => ['required', 'numeric', 'min:0'],
        ]);

        $landlordId = $this->landlordId();

        DB::transaction(function () use ($validated, $landlordId) {

            $property = Property::create([
                'landlord_id' => $landlordId,
                'property_name' => $validated['property_name'],
                'property_code' => $validated['property_code'] ?? null,
                'property_type' => $validated['property_type'],
                'address' => $validated['address'] ?? null,
                'town' => $validated['town'] ?? null,
                'county' => $validated['county'] ?? null,
                'description' => $validated['description'] ?? null,
                'unit_prefix' => strtoupper($validated['unit_prefix']),
                'default_rent' => $validated['default_rent'],
                'default_deposit' => $validated['default_deposit'],
                'default_garbage_fee' => $validated['default_garbage_fee'],
                'is_active' => true,
            ]);

            $units = [];

            for ($i = 1; $i <= $validated['units_count']; $i++) {
                $units[] = [
                    'unit_number' => strtoupper($validated['unit_prefix']).$i,
                    'unit_type' => null,
                    'monthly_rent' => $validated['default_rent'],
                    'deposit_amount' => $validated['default_deposit'],
                    'garbage_fee' => $validated['default_garbage_fee'],
                    'status' => 'vacant',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $property->units()->createMany($units);
        });

        return redirect()
            ->route('properties.index')
            ->with('success', 'Property and units created successfully.');
    }

    public function show(Request $request, Property $property)
    {
        abort_unless(
            $property->landlord_id === $this->landlordId(),
            404
        );

        $unitNumberSuffixStart = strlen($property->unit_prefix ?? '') + 1;

        $units = $property->units()
            ->with([
                'currentTenancy.tenant',
                'vacancyListing',
                'tenancies' => fn ($query) => $query
                    ->whereIn('status', ['active', 'upcoming']),
            ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('unit_number', 'like', '%'.$request->search.'%');
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderByRaw(
                'CAST(SUBSTRING(unit_number, ?) AS UNSIGNED) ASC',
                [$unitNumberSuffixStart]
            )
            ->orderBy('unit_number', 'asc')
            ->paginate(15)
            ->withQueryString();

        $unitStats = [
            'total' => $property->units()->count(),
            'occupied' => $property->units()->where('status', 'occupied')->count(),
            'vacant' => $property->units()->where('status', 'vacant')->count(),
            'maintenance' => $property->units()->where('status', 'maintenance')->count(),
        ];

        return view('properties.show', compact('property', 'units', 'unitStats'));
    }

    public function edit(Property $property)
    {
        abort_unless(
            $property->landlord_id === $this->landlordId(),
            404
        );

        return view('properties.edit', compact('property'));
    }

    public function update(Request $request, Property $property)
    {
        abort_unless(
            $property->landlord_id === $this->landlordId(),
            404
        );

        $validated = $request->validate([
            'property_name' => ['required', 'string', 'max:255'],
            'property_code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('properties', 'property_code')->ignore($property->id),
            ],
            'property_type' => ['required', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'town' => ['nullable', 'string', 'max:255'],
            'county' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'unit_prefix' => ['required', 'alpha_dash', 'max:20'],
            'default_rent' => ['required', 'numeric', 'min:0'],
            'default_deposit' => ['required', 'numeric', 'min:0'],
            'default_garbage_fee' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $property->update($validated);

        return redirect()
            ->route('properties.show', $property)
            ->with('success', 'Property updated successfully.');
    }

    public function toggleStatus(Property $property)
    {
        abort_unless(
            $property->landlord_id === $this->landlordId(),
            404
        );

        $property->update([
            'is_active' => ! $property->is_active,
        ]);

        return back()->with('success', 'Property status updated.');
    }
}
