
@extends('layouts.landlord')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div>
        <a href="{{ route('tenancies.index') }}"
           class="btn btn-outline">
            ← Back to Tenancies
        </a>

        <h1 class="text-2xl font-bold text-gray-800 mt-3">Create New Tenancy</h1>
        <p class="text-gray-500 mt-1">
            Register a tenancy agreement and allocate a vacant unit.
        </p>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg">
            <p class="font-semibold mb-2">Please correct the following:</p>
            <ul class="list-disc pl-5 text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($tenants->isEmpty())
        <div class="page-card">
            <p class="text-gray-700">You have not registered any tenants yet.</p>
            <a href="{{ route('tenants.create') }}"
               class="btn btn-primary inline-block mt-4">
                Register Tenant
            </a>
        </div>
    @elseif($properties->isEmpty() || $properties->every(fn($property) => $property->units->isEmpty()))
        <div class="page-card">
            <p class="text-gray-700">
                No vacant units are currently available for allocation.
            </p>
            <a href="{{ route('properties.index') }}"
               class="btn btn-primary inline-block mt-4">
                View Properties
            </a>
        </div>
    @else

    <form method="POST" action="{{ route('tenancies.store') }}"
          class="space-y-6">
        @csrf

        <div class="page-card space-y-5">
            <h2 class="text-lg font-bold text-gray-800 border-b pb-3">
                1. Tenant and Unit Allocation
            </h2>

            <div>
                <label class="block font-semibold mb-2">Select Tenant *</label>
                <select name="tenant_id" required class="form-control">
                    <option value="">Choose a registered tenant</option>
                    @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}"
                            @selected(old('tenant_id') == $tenant->id)>
                            {{ $tenant->first_name }}
                            {{ $tenant->middle_name }}
                            {{ $tenant->last_name }}
                            — {{ $tenant->phone }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block font-semibold mb-2">Property *</label>
                    <select name="property_id" id="property_id"
                            required class="form-control">
                        <option value="">Select property</option>
                        @foreach($properties as $property)
                            <option value="{{ $property->id }}"
                                    @selected(old('property_id') == $property->id)>
                                {{ $property->property_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold mb-2">Vacant Unit *</label>
                    <select name="unit_id" id="unit_id"
                            required class="form-control">
                        <option value="">Select a property first</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">
                        Only vacant, unreserved units are displayed.
                    </p>
                </div>
            </div>
        </div>

        <div class="page-card space-y-5">
            <h2 class="text-lg font-bold text-gray-800 border-b pb-3">
                2. Agreed Financial Terms
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block font-semibold mb-2">Monthly Rent (KES) *</label>
                    <input type="number" name="monthly_rent" id="monthly_rent"
                           min="0" step="0.01" required
                           value="{{ old('monthly_rent') }}"
                           class="form-control">
                </div>

                <div>
                    <label class="block font-semibold mb-2">Deposit (KES) *</label>
                    <input type="number" name="deposit_amount" id="deposit_amount"
                           min="0" step="0.01" required
                           value="{{ old('deposit_amount') }}"
                           class="form-control">
                </div>

                <div>
                    <label class="block font-semibold mb-2">Garbage Fee (KES) *</label>
                    <input type="number" name="garbage_fee" id="garbage_fee"
                           min="0" step="0.01" required
                           value="{{ old('garbage_fee') }}"
                           class="form-control">
                </div>
            </div>
        </div>

        <div class="page-card space-y-5">
            <h2 class="text-lg font-bold text-gray-800 border-b pb-3">
                3. Tenancy Period and Payment Schedule
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block font-semibold mb-2">Start Date *</label>
                    <input type="date" name="start_date" required
                           value="{{ old('start_date', now()->toDateString()) }}"
                           class="form-control">
                </div>

                <div>
                    <label class="block font-semibold mb-2">Lease End Date (Optional)</label>
                    <input type="date" name="lease_end_date"
                           value="{{ old('lease_end_date') }}"
                           class="form-control">
                </div>

                <div>
                    <label class="block font-semibold mb-2">Rent Due Day *</label>
                    <select name="rent_due_day" required class="form-control">
                        <option value="">Select due day</option>
                        @for($day = 1; $day <= 28; $day++)
                            <option value="{{ $day }}"
                                @selected(old('rent_due_day', 1) == $day)>
                                {{ $day }}{{ $day === 1 ? 'st' : ($day === 2 ? 'nd' : ($day === 3 ? 'rd' : 'th')) }} of every month
                            </option>
                        @endfor
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-semibold mb-2">Additional Notes</label>
                <textarea name="notes" rows="4" class="form-control"
                          placeholder="Optional tenancy agreement notes...">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-end gap-3">
            <a href="{{ route('tenancies.index') }}"
               class="btn bg-gray-200 text-gray-700 text-center">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary">
                Create Tenancy & Allocate Unit
            </button>
        </div>
    </form>
    @endif
</div>

@php
    $propertyOptions = $properties->map(function ($property) {
        return [
            'id' => $property->id,
            'units' => $property->units->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'unit_number' => $unit->unit_number,
                    'monthly_rent' => $unit->monthly_rent,
                    'deposit_amount' => $unit->deposit_amount,
                    'garbage_fee' => $unit->garbage_fee,
                ];
            })->values(),
        ];
    })->values();
@endphp

<script>
document.addEventListener('DOMContentLoaded', function () {
    const properties = @json($propertyOptions);

    const propertySelect = document.getElementById('property_id');
    const unitSelect = document.getElementById('unit_id');

    const rentInput = document.getElementById('monthly_rent');
    const depositInput = document.getElementById('deposit_amount');
    const garbageInput = document.getElementById('garbage_fee');

    const oldUnit = @json(old('unit_id'));
    const oldRent = @json(old('monthly_rent'));
    const oldDeposit = @json(old('deposit_amount'));
    const oldGarbage = @json(old('garbage_fee'));

    function loadUnits(selectedUnit = null, applyDefaults = true) {
        unitSelect.innerHTML = '<option value="">Select vacant unit</option>';

        const property = properties.find(
            item => String(item.id) === String(propertySelect.value)
        );

        if (!property) return;

        property.units.forEach(unit => {
            const option = document.createElement('option');
            option.value = unit.id;
            option.textContent = unit.unit_number;
            option.dataset.rent = unit.monthly_rent;
            option.dataset.deposit = unit.deposit_amount;
            option.dataset.garbage = unit.garbage_fee;

            if (String(unit.id) === String(selectedUnit)) {
                option.selected = true;
            }

            unitSelect.appendChild(option);
        });

        if (applyDefaults && unitSelect.value) {
            applyUnitDefaults();
        }
    }

    function applyUnitDefaults() {
        const selected = unitSelect.options[unitSelect.selectedIndex];

        if (!selected || !selected.value) return;

        rentInput.value = selected.dataset.rent ?? 0;
        depositInput.value = selected.dataset.deposit ?? 0;
        garbageInput.value = selected.dataset.garbage ?? 0;
    }

    propertySelect.addEventListener('change', function () {
        loadUnits();
    });

    unitSelect.addEventListener('change', applyUnitDefaults);

    if (propertySelect.value) {
        loadUnits(oldUnit, false);
    }

    if (oldRent !== null && oldRent !== '') rentInput.value = oldRent;
    if (oldDeposit !== null && oldDeposit !== '') depositInput.value = oldDeposit;
    if (oldGarbage !== null && oldGarbage !== '') garbageInput.value = oldGarbage;
});
</script>
@endsection
