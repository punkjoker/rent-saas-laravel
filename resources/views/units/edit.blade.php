
@extends('layouts.landlord')

@section('title', 'Edit Unit')
@section('page-heading', 'Edit Rental Unit')
@section('page-description', 'Update the house information and rental charges.')

@section('content')

<div style="margin-bottom:20px;">
    <a href="{{ route('properties.show', $property) }}"
       style="color:#176b55;text-decoration:none;font-size:13px;font-weight:700;">
        ← Back to {{ $property->property_name }}
    </a>
</div>

<div class="page-card">

    <h3 style="margin-bottom:7px;">
        {{ $unit->unit_number }}
    </h3>

    <p style="color:#849087;font-size:13px;margin-bottom:22px;">
        Property: {{ $property->property_name }}
    </p>

    <form method="POST" action="{{ route('units.update', $unit) }}">

        @csrf
        @method('PUT')

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;">

            <div class="form-group">
                <label>House Number *</label>
                <input type="text" name="unit_number"
                       value="{{ old('unit_number', $unit->unit_number) }}"
                       class="form-control" required>
            </div>

            <div class="form-group">
                <label>Unit Type</label>
                <select name="unit_type" class="form-control">
                    <option value="">Select unit type</option>
                    @foreach(['Bedsitter', 'Studio', 'One Bedroom', 'Two Bedroom', 'Three Bedroom', 'Commercial', 'Other'] as $type)
                        <option value="{{ $type }}"
                            {{ old('unit_type', $unit->unit_type) === $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Monthly Rent (KES) *</label>
                <input type="number" name="monthly_rent"
                       value="{{ old('monthly_rent', $unit->monthly_rent) }}"
                       class="form-control" min="0" step="0.01" required>
            </div>

            <div class="form-group">
                <label>Security Deposit (KES) *</label>
                <input type="number" name="deposit_amount"
                       value="{{ old('deposit_amount', $unit->deposit_amount) }}"
                       class="form-control" min="0" step="0.01" required>
            </div>

            <div class="form-group">
                <label>Monthly Garbage Fee (KES) *</label>
                <input type="number" name="garbage_fee"
                       value="{{ old('garbage_fee', $unit->garbage_fee) }}"
                       class="form-control" min="0" step="0.01" required>
            </div>

            <div class="form-group">
                <label>Occupancy Status *</label>
                <select name="status" class="form-control" required>
                    <option value="vacant" {{ old('status', $unit->status) === 'vacant' ? 'selected' : '' }}>Vacant</option>
                    <option value="occupied" {{ old('status', $unit->status) === 'occupied' ? 'selected' : '' }}>Occupied</option>
                    <option value="maintenance" {{ old('status', $unit->status) === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                </select>
            </div>

        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3"
                      class="form-control"
                      placeholder="Additional unit information...">{{ old('description', $unit->description) }}</textarea>
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end;flex-wrap:wrap;">

            <a href="{{ route('properties.show', $property) }}"
               class="btn btn-secondary">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary">
                Save Unit Changes
            </button>

        </div>

    </form>

</div>

@endsection
