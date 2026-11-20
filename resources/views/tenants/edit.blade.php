
@extends('layouts.landlord')

@section('title', 'Edit Tenant')
@section('page-heading', 'Edit Tenant Details')
@section('page-description', 'Update the tenant personal and contact information.')

@section('content')

<div class="page-card">

    <form method="POST"
          action="{{ route('tenants.update', $tenant->id) }}">

        @csrf
        @method('PUT')

        <h3 style="margin-bottom:20px;">Personal Information</h3>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;">

            <div class="form-group">
                <label>First Name *</label>
                <input type="text" name="first_name"
                       class="form-control"
                       value="{{ old('first_name', $tenant->first_name) }}"
                       maxlength="100" required>
            </div>

            <div class="form-group">
                <label>Middle Name</label>
                <input type="text" name="middle_name"
                       class="form-control"
                       value="{{ old('middle_name', $tenant->middle_name) }}"
                       maxlength="100">
            </div>

            <div class="form-group">
                <label>Last Name *</label>
                <input type="text" name="last_name"
                       class="form-control"
                       value="{{ old('last_name', $tenant->last_name) }}"
                       maxlength="100" required>
            </div>

            <div class="form-group">
                <label>Date of Birth</label>
                <input type="date" name="date_of_birth"
                       class="form-control"
                       value="{{ old('date_of_birth', $tenant->date_of_birth?->format('Y-m-d')) }}"
                       max="{{ now()->subDay()->format('Y-m-d') }}">
            </div>

            <div class="form-group">
                <label>Occupation</label>
                <input type="text" name="occupation"
                       class="form-control"
                       value="{{ old('occupation', $tenant->occupation) }}"
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

                    <option value="national_id"
                        @selected(old('identification_type', $tenant->identification_type) === 'national_id')>
                        National ID
                    </option>

                    <option value="passport"
                        @selected(old('identification_type', $tenant->identification_type) === 'passport')>
                        Passport
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label>Identification Number</label>
                <input type="text" name="identification_number"
                       class="form-control"
                       value="{{ old('identification_number', $tenant->identification_number) }}"
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
                       value="{{ old('phone', $tenant->phone) }}"
                       maxlength="30" required>
            </div>

            <div class="form-group">
                <label>Alternative Phone Number</label>
                <input type="tel" name="alternative_phone"
                       class="form-control"
                       value="{{ old('alternative_phone', $tenant->alternative_phone) }}"
                       maxlength="30">
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email"
                       class="form-control"
                       value="{{ old('email', $tenant->email) }}"
                       maxlength="255">
            </div>

        </div>

        <div class="form-group">
            <label>Residential / Postal Address</label>
            <textarea name="address" rows="3"
                      class="form-control">{{ old('address', $tenant->address) }}</textarea>
        </div>

        <hr style="border:0;border-top:1px solid #edf1ee;margin:24px 0;">

        <h3 style="margin-bottom:20px;">Emergency Contact</h3>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;">

            <div class="form-group">
                <label>Emergency Contact Name</label>
                <input type="text" name="emergency_contact_name"
                       class="form-control"
                       value="{{ old('emergency_contact_name', $tenant->emergency_contact_name) }}"
                       maxlength="150">
            </div>

            <div class="form-group">
                <label>Emergency Contact Phone</label>
                <input type="tel" name="emergency_contact_phone"
                       class="form-control"
                       value="{{ old('emergency_contact_phone', $tenant->emergency_contact_phone) }}"
                       maxlength="30">
            </div>

        </div>

        <div class="form-group">
            <label>Additional Notes</label>
            <textarea name="notes" rows="4"
                      class="form-control">{{ old('notes', $tenant->notes) }}</textarea>
        </div>

        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:25px;">

            <button type="submit" class="btn btn-primary">
                Update Tenant
            </button>

            <a href="{{ route('tenants.show', $tenant->id) }}"
               class="btn btn-secondary">
                Cancel
            </a>

        </div>

    </form>

</div>

@endsection
