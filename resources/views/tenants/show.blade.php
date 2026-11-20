
@extends('layouts.landlord')

@section('title', 'Tenant Profile')
@section('page-heading', 'Tenant Profile')
@section('page-description', 'View tenant personal and contact information.')

@section('content')

<div class="page-card">

    <div style="display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap;margin-bottom:25px;">

        <div>
            <h2 style="font-size:23px;color:#203c30;">
                {{ $tenant->full_name }}
            </h2>

            <p style="color:#849087;font-size:13px;margin-top:5px;">
                Registered {{ $tenant->created_at->format('d M Y') }}
            </p>
        </div>

        <a href="{{ route('tenants.edit', $tenant->id) }}"
           class="btn btn-primary">
            Edit Tenant
        </a>

    </div>

    <h3 style="margin-bottom:18px;">Personal Information</h3>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:22px;">

        <div>
            <small style="color:#849087;">First Name</small>
            <p>{{ $tenant->first_name }}</p>
        </div>

        <div>
            <small style="color:#849087;">Middle Name</small>
            <p>{{ $tenant->middle_name ?: '—' }}</p>
        </div>

        <div>
            <small style="color:#849087;">Last Name</small>
            <p>{{ $tenant->last_name }}</p>
        </div>

        <div>
            <small style="color:#849087;">Date of Birth</small>
            <p>{{ $tenant->date_of_birth?->format('d M Y') ?? '—' }}</p>
        </div>

        <div>
            <small style="color:#849087;">Occupation</small>
            <p>{{ $tenant->occupation ?: '—' }}</p>
        </div>

    </div>

    <hr style="border:0;border-top:1px solid #edf1ee;margin:25px 0;">

    <h3 style="margin-bottom:18px;">Identification Details</h3>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:22px;">

        <div>
            <small style="color:#849087;">Identification Type</small>
            <p>
                @if($tenant->identification_type === 'national_id')
                    National ID
                @elseif($tenant->identification_type === 'passport')
                    Passport
                @else
                    —
                @endif
            </p>
        </div>

        <div>
            <small style="color:#849087;">Identification Number</small>
            <p>{{ $tenant->identification_number ?: '—' }}</p>
        </div>

    </div>

    <hr style="border:0;border-top:1px solid #edf1ee;margin:25px 0;">

    <h3 style="margin-bottom:18px;">Contact Information</h3>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:22px;">

        <div>
            <small style="color:#849087;">Primary Phone</small>
            <p>{{ $tenant->phone }}</p>
        </div>

        <div>
            <small style="color:#849087;">Alternative Phone</small>
            <p>{{ $tenant->alternative_phone ?: '—' }}</p>
        </div>

        <div>
            <small style="color:#849087;">Email</small>
            <p>{{ $tenant->email ?: '—' }}</p>
        </div>

        <div>
            <small style="color:#849087;">Address</small>
            <p>{{ $tenant->address ?: '—' }}</p>
        </div>

    </div>

    <hr style="border:0;border-top:1px solid #edf1ee;margin:25px 0;">

    <h3 style="margin-bottom:18px;">Emergency Contact</h3>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:22px;">

        <div>
            <small style="color:#849087;">Contact Name</small>
            <p>{{ $tenant->emergency_contact_name ?: '—' }}</p>
        </div>

        <div>
            <small style="color:#849087;">Contact Phone</small>
            <p>{{ $tenant->emergency_contact_phone ?: '—' }}</p>
        </div>

    </div>

    <hr style="border:0;border-top:1px solid #edf1ee;margin:25px 0;">

    <h3 style="margin-bottom:12px;">Additional Notes</h3>

    <p style="color:#53645a;line-height:1.7;white-space:pre-line;">
        {{ $tenant->notes ?: 'No additional notes.' }}
    </p>

    <div style="margin-top:30px;">
        <a href="{{ route('tenants.index') }}"
           class="btn btn-secondary">
            Back to Tenants
        </a>
    </div>

</div>

@endsection
