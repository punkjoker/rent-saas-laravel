
@extends('layouts.landlord')

@section('title', 'Edit Property')
@section('page-heading', 'Edit Property')
@section('page-description', 'Update your property information and default rental settings.')

@section('content')

<div style="margin-bottom:20px;">
    <a href="{{ route('properties.show', $property) }}"
       style="color:#176b55;text-decoration:none;font-size:13px;font-weight:700;">
        ← Back to Property
    </a>
</div>

<div class="page-card">

    <form method="POST" action="{{ route('properties.update', $property) }}">

        @csrf
        @method('PUT')

        <h3 style="margin-bottom:20px;">Property Information</h3>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;">

            <div class="form-group">
                <label>Property Name *</label>
                <input type="text" name="property_name"
                       value="{{ old('property_name', $property->property_name) }}"
                       class="form-control" required>
            </div>

            <div class="form-group">
                <label>Property Code</label>
                <input type="text" name="property_code"
                       value="{{ old('property_code', $property->property_code) }}"
                       class="form-control">
            </div>

            <div class="form-group">
                <label>Property Type *</label>
                <select name="property_type" class="form-control" required>
                    @foreach(['apartment', 'flats', 'bedsitters', 'residential', 'commercial', 'mixed'] as $type)
                        <option value="{{ $type }}"
                            {{ old('property_type', $property->property_type) === $type ? 'selected' : '' }}>
                            {{ ucfirst($type) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Town</label>
                <input type="text" name="town"
                       value="{{ old('town', $property->town) }}"
                       class="form-control">
            </div>

            <div class="form-group">
                <label>County</label>
                <input type="text" name="county"
                       value="{{ old('county', $property->county) }}"
                       class="form-control">
            </div>

            <div class="form-group">
                <label>Address</label>
                <input type="text" name="address"
                       value="{{ old('address', $property->address) }}"
                       class="form-control">
            </div>

        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3"
                      class="form-control">{{ old('description', $property->description) }}</textarea>
        </div>

        <hr style="border:0;border-top:1px solid #edf1ee;margin:25px 0;">

        <h3 style="margin-bottom:18px;">Default Rental Settings</h3>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;">

            <div class="form-group">
                <label>Unit Prefix *</label>
                <input type="text" name="unit_prefix"
                       value="{{ old('unit_prefix', $property->unit_prefix) }}"
                       class="form-control" required>
            </div>

            <div class="form-group">
                <label>Default Monthly Rent (KES) *</label>
                <input type="number" name="default_rent"
                       value="{{ old('default_rent', $property->default_rent) }}"
                       class="form-control" min="0" step="0.01" required>
            </div>

            <div class="form-group">
                <label>Default Deposit (KES) *</label>
                <input type="number" name="default_deposit"
                       value="{{ old('default_deposit', $property->default_deposit) }}"
                       class="form-control" min="0" step="0.01" required>
            </div>

            <div class="form-group">
                <label>Default Garbage Fee (KES) *</label>
                <input type="number" name="default_garbage_fee"
                       value="{{ old('default_garbage_fee', $property->default_garbage_fee) }}"
                       class="form-control" min="0" step="0.01" required>
            </div>

            <div class="form-group">
                <label>Property Status *</label>
                <select name="is_active" class="form-control" required>
                    <option value="1" {{ old('is_active', $property->is_active ? '1' : '0') == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active', $property->is_active ? '1' : '0') == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

        </div>

        <div style="background:#fff7e8;padding:14px;border-radius:8px;margin-bottom:20px;color:#805b25;font-size:12px;">
            Changing the default rental rates will not automatically change existing units. Edit individual units to change their current rates.
        </div>

        <div style="display:flex;justify-content:flex-end;gap:12px;">
            <a href="{{ route('properties.show', $property) }}"
               class="btn btn-secondary">Cancel</a>

            <button type="submit" class="btn btn-primary">
                Update Property
            </button>
        </div>

    </form>

</div>

@endsection
