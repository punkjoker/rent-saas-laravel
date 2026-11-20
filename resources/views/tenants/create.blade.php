
@extends('layouts.landlord')

@section('title', 'Register Tenant')
@section('page-heading', 'Register New Tenant')
@section('page-description', 'Enter the tenant personal and contact information.')

@section('content')

<div class="page-card">

    <form method="POST" action="{{ route('tenants.store') }}">
        @csrf

        <h3 style="margin-bottom:20px;">Personal Information</h3>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;">

            <div class="form-group">
                <label>First Name *</label>
                <input type="text" name="first_name"
                       class="form-control"
                       value="{{ old('first_name') }}"
                       maxlength="100" required>
            </div>

            <div class="form-group">
                <label>Middle Name</label>
                <input type="text" name="middle_name"
                       class="form-control"
                       value="{{ old('middle_name') }}"
                       maxlength="100">
            </div>

            <div class="form-group">
                <label>Last Name *</label>
                <input type="text" name="last_name"
                       class="form-control"
                       value="{{ old('last_name') }}"
                       maxlength="100" required>
            </div>

            <div class="form-group">
                <label>Date of Birth</label>
                <input type="date" name="date_of_birth"
                       class="form-control"
                       value="{{ old('date_of_birth') }}"
                       max="{{ now()->subDay()->format('Y-m-d') }}">
            </div>

            <div class="form-group">
                <label>Occupation</label>
                <input type="text" name="occupation"
                       class="form-control"
                       value="{{ old('occupation') }}"
                       maxlength="150">
            </div>

        </div>

        <hr style="border:0;border-top:1px solid #edf1ee;margin:12px 0 24px;">

        <h3 style="margin-bottom:20px;">Identification Details</h3>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;">

            <div class="form-group">
                <label>Identification Type</label>
                <select name="identification_type" class="form-control">
                    <option value="">Select identification type</option>
                    <option value="national_id" @selected(old('identification_type') === 'national_id')>
                        National ID
                    </option>
                    <option value="passport" @selected(old('identification_type') === 'passport')>
                        Passport
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label>Identification Number</label>
                <input type="text" name="identification_number"
                       class="form-control"
                       value="{{ old('identification_number') }}"
                       maxlength="100">
            </div>

        </div>

        <hr style="border:0;border-top:1px solid #edf1ee;margin:12px 0 24px;">

        <h3 style="margin-bottom:20px;">Contact Information</h3>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;">

            <div class="form-group">
                <label>Primary Phone Number *</label>
                <input type="tel" name="phone"
                       class="form-control"
                       value="{{ old('phone') }}"
                       maxlength="30" required
                       placeholder="e.g. 0712345678">
            </div>

            <div class="form-group">
                <label>Alternative Phone Number</label>
                <input type="tel" name="alternative_phone"
                       class="form-control"
                       value="{{ old('alternative_phone') }}"
                       maxlength="30">
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email"
                       class="form-control"
                       value="{{ old('email') }}"
                       maxlength="255">
            </div>

        </div>

        <div class="form-group">
            <label>Residential / Postal Address</label>
            <textarea name="address" rows="3"
                      class="form-control">{{ old('address') }}</textarea>
        </div>

        <hr style="border:0;border-top:1px solid #edf1ee;margin:24px 0;">

        <h3 style="margin-bottom:20px;">Emergency Contact</h3>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;">

            <div class="form-group">
                <label>Emergency Contact Name</label>
                <input type="text" name="emergency_contact_name"
                       class="form-control"
                       value="{{ old('emergency_contact_name') }}"
                       maxlength="150">
            </div>

            <div class="form-group">
                <label>Emergency Contact Phone</label>
                <input type="tel" name="emergency_contact_phone"
                       class="form-control"
                       value="{{ old('emergency_contact_phone') }}"
                       maxlength="30">
            </div>

        </div>

        <div class="form-group">
            <label>Additional Notes</label>
            <textarea name="notes" rows="4"
                      class="form-control"
                      placeholder="Any additional information about the tenant...">{{ old('notes') }}</textarea>
        </div>

        <hr style="border:0;border-top:1px solid #edf1ee;margin:24px 0;">

        <h3 style="margin-bottom:8px;">Optional Apartment Assignment</h3>
        <p style="font-size:13px;color:#849087;margin-bottom:18px;">
            Select an apartment and vacant house to register this tenant and start their tenancy today.
            Rent and fees will use the selected unit's current amounts.
        </p>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;">
            <div class="form-group">
                <label for="property_id">Apartment</label>
                <select id="property_id" name="property_id" class="form-control">
                    <option value="">Register tenant without assigning a house</option>
                    @foreach($propertyOptions as $property)
                        <option value="{{ $property['id'] }}"
                            @selected(old('property_id') == $property['id'])>
                            {{ $property['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="unit_id">House Number</label>
                <select id="unit_id" name="unit_id" class="form-control" disabled>
                    <option value="">Select an apartment first</option>
                </select>
            </div>
        </div>
        @if($propertyOptions->isEmpty())
            <p class="form-help">
                No vacant houses are available right now. You can register the tenant and assign a house later.
            </p>
        @endif

        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:25px;">

            <button type="submit" class="btn btn-primary">
                Save Tenant
            </button>

            <a href="{{ route('tenants.index') }}"
               class="btn btn-secondary">
                Cancel
            </a>

        </div>

    </form>

</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const properties = @json($propertyOptions);
    const propertySelect = document.getElementById('property_id');
    const unitSelect = document.getElementById('unit_id');
    const oldUnit = @json(old('unit_id'));

    function loadUnits(selectedUnit = '') {
        unitSelect.innerHTML = '<option value="">Select a vacant house</option>';

        const property = properties.find(function (item) {
            return String(item.id) === String(propertySelect.value);
        });

        unitSelect.disabled = !property;

        if (!property) {
            unitSelect.innerHTML = '<option value="">Select an apartment first</option>';
            return;
        }

        property.units.forEach(function (unit) {
            const option = document.createElement('option');
            option.value = unit.id;
            option.textContent = unit.number;
            option.selected = String(unit.id) === String(selectedUnit);
            unitSelect.appendChild(option);
        });
    }

    propertySelect.addEventListener('change', function () {
        loadUnits();
    });

    loadUnits(oldUnit);
});
</script>
@endpush
