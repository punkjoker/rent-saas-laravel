
@extends('layouts.landlord')

@section('title', 'Add Property')
@section('page-heading', 'Register New Property')
@section('page-description', 'Add an apartment and configure its rental units.')

@section('content')

<div style="margin-bottom:20px;">
    <a href="{{ route('properties.index') }}"
       style="color:#176b55;text-decoration:none;font-size:13px;font-weight:700;">
        ← Back to Properties
    </a>
</div>

<form method="POST" action="{{ route('properties.store') }}">
    @csrf

    <div class="page-card">

        <h3 style="margin-bottom:6px;">Property Information</h3>

        <p style="font-size:13px;color:#849087;margin-bottom:22px;">
            Enter the basic details of your apartment or rental property.
        </p>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:18px;">

            <div class="form-group">
                <label>Property Name *</label>
                <input type="text" name="property_name"
                       class="form-control"
                       value="{{ old('property_name') }}"
                       placeholder="e.g. Nelly Apartments" required>
            </div>

            <div class="form-group">
                <label>Property Code</label>
                <input type="text" name="property_code"
                       class="form-control"
                       value="{{ old('property_code') }}"
                       placeholder="e.g. NELLY001">
                <small class="form-help">Optional unique property reference.</small>
            </div>

            <div class="form-group">
                <label>Property Type *</label>
                <select name="property_type" class="form-control" required>
                    <option value="apartment" {{ old('property_type', 'apartment') === 'apartment' ? 'selected' : '' }}>Apartment</option>
                    <option value="flats" {{ old('property_type') === 'flats' ? 'selected' : '' }}>Flats</option>
                    <option value="bedsitters" {{ old('property_type') === 'bedsitters' ? 'selected' : '' }}>Bedsitters</option>
                    <option value="residential" {{ old('property_type') === 'residential' ? 'selected' : '' }}>Residential Houses</option>
                    <option value="commercial" {{ old('property_type') === 'commercial' ? 'selected' : '' }}>Commercial</option>
                    <option value="mixed" {{ old('property_type') === 'mixed' ? 'selected' : '' }}>Mixed Use</option>
                </select>
            </div>

            <div class="form-group">
                <label>Town</label>
                <input type="text" name="town"
                       class="form-control"
                       value="{{ old('town') }}"
                       placeholder="e.g. Juja">
            </div>

            <div class="form-group">
                <label>County</label>
                <input type="text" name="county"
                       class="form-control"
                       value="{{ old('county') }}"
                       placeholder="e.g. Kiambu">
            </div>

            <div class="form-group">
                <label>Physical Address</label>
                <input type="text" name="address"
                       class="form-control"
                       value="{{ old('address') }}"
                       placeholder="Street, estate or building location">
            </div>

        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3"
                      class="form-control"
                      placeholder="Additional property information...">{{ old('description') }}</textarea>
        </div>

    </div>

    <div class="page-card">

        <h3 style="margin-bottom:6px;">Unit Configuration</h3>

        <p style="font-size:13px;color:#849087;margin-bottom:22px;">
            Configure the house numbering and the number of rental units.
        </p>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;">

            <div class="form-group">
                <label>House Number Prefix *</label>
                <input type="text" id="unit_prefix"
                       name="unit_prefix"
                       class="form-control"
                       value="{{ old('unit_prefix', 'HSE') }}"
                       maxlength="20"
                       pattern="[A-Za-z0-9_-]+"
                       placeholder="HSE" required>
                <small class="form-help">Examples: HSE, D, APT, B1.</small>
            </div>

            <div class="form-group">
                <label>Number of Units *</label>
                <input type="number" id="units_count"
                       name="units_count"
                       class="form-control"
                       value="{{ old('units_count', 20) }}"
                       min="1" max="500" required>
                <small class="form-help">You can add more units later.</small>
            </div>

        </div>

        <div style="background:#f0f7f3;border:1px solid #dcebe1;padding:17px;border-radius:10px;">

            <strong style="font-size:13px;color:#176b55;">
                Automatic House Number Preview
            </strong>

            <p id="unit_preview"
               style="font-size:13px;color:#52685b;margin-top:10px;line-height:2;">
            </p>

        </div>

    </div>

    <div class="page-card">

        <h3 style="margin-bottom:6px;">Default Rental Charges</h3>

        <p style="font-size:13px;color:#849087;margin-bottom:22px;">
            These amounts will initially apply to every generated unit. You can change each house independently later.
        </p>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;">

            <div class="form-group">
                <label>Default Monthly Rent (KES) *</label>
                <input type="number" name="default_rent"
                       class="form-control"
                       value="{{ old('default_rent', 8000) }}"
                       min="0" step="0.01" required>
            </div>

            <div class="form-group">
                <label>Default Security Deposit (KES) *</label>
                <input type="number" name="default_deposit"
                       class="form-control"
                       value="{{ old('default_deposit', 8000) }}"
                       min="0" step="0.01" required>
            </div>

            <div class="form-group">
                <label>Monthly Garbage Fee (KES) *</label>
                <input type="number" name="default_garbage_fee"
                       class="form-control"
                       value="{{ old('default_garbage_fee', 300) }}"
                       min="0" step="0.01" required>
            </div>

        </div>

    </div>

    <div style="display:flex;justify-content:flex-end;gap:12px;flex-wrap:wrap;margin-bottom:30px;">

        <a href="{{ route('properties.index') }}" class="btn btn-secondary">
            Cancel
        </a>

        <button type="submit" class="btn btn-primary">
            Save Property & Generate Units
        </button>

    </div>

</form>

@endsection

@push('styles')
<style>
    #unit_preview {
        overflow-wrap: anywhere;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const prefixInput = document.getElementById('unit_prefix');
    const countInput = document.getElementById('units_count');
    const preview = document.getElementById('unit_preview');

    function updatePreview() {
        const prefix = prefixInput.value.trim().toUpperCase() || 'HSE';
        const count = Math.max(0, Math.min(500, parseInt(countInput.value, 10) || 0));

        if (!count) {
            preview.textContent = 'Enter the number of units to see the preview.';
            return;
        }

        const examples = [];

        for (let i = 1; i <= Math.min(count, 8); i++) {
            examples.push(prefix + i);
        }

        if (count > 8) {
            examples.push('...');
            examples.push(prefix + count);
        }

        preview.textContent = examples.join(' • ');
    }

    prefixInput.addEventListener('input', updatePreview);
    countInput.addEventListener('input', updatePreview);

    updatePreview();
});
</script>
@endpush
