
@extends('layouts.landlord')

@section('title', $property->property_name)
@section('page-heading', $property->property_name)
@section('page-description', 'Property overview and rental unit management.')

@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:22px;">

    <a href="{{ route('properties.index') }}"
       style="color:#176b55;text-decoration:none;font-size:13px;font-weight:700;">
        ← Back to Properties
    </a>

    <a href="{{ route('properties.edit', $property) }}" class="btn btn-primary">
        Edit Property
    </a>

</div>

<div class="page-card">

    <div style="display:flex;justify-content:space-between;gap:15px;flex-wrap:wrap;">

        <div>
            <h2 style="font-size:22px;">{{ $property->property_name }}</h2>

            <p style="color:#849087;font-size:13px;margin-top:7px;">
                {{ $property->address ?: 'Address not specified' }}
                · {{ $property->town ?: '' }}
                {{ $property->county ? ', '.$property->county : '' }}
            </p>

            <p style="color:#849087;font-size:12px;margin-top:8px;">
                Property Code: {{ $property->property_code ?: 'N/A' }}
                · Type: {{ ucfirst($property->property_type) }}
            </p>
        </div>

        <div>
            <span class="status {{ $property->is_active ? 'status-active' : 'status-inactive' }}">
                {{ $property->is_active ? 'Active Property' : 'Inactive Property' }}
            </span>
        </div>

    </div>

    <div style="margin-top:20px;padding-top:18px;border-top:1px solid #edf1ee;">

        <form method="POST"
              action="{{ route('properties.toggle-status', $property) }}"
              onsubmit="return confirm('Are you sure you want to change this property status?');">

            @csrf
            @method('PATCH')

            <button type="submit" class="btn btn-secondary">
                {{ $property->is_active ? 'Deactivate Property' : 'Activate Property' }}
            </button>

        </form>

    </div>

</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:15px;margin-bottom:22px;">

    <div class="page-card">
        <small style="color:#849087;">Total Units</small>
        <h2 style="font-size:28px;margin-top:10px;">{{ $unitStats['total'] }}</h2>
    </div>

    <div class="page-card">
        <small style="color:#849087;">Occupied</small>
        <h2 style="font-size:28px;color:#238060;margin-top:10px;">{{ $unitStats['occupied'] }}</h2>
    </div>

    <div class="page-card">
        <small style="color:#849087;">Vacant</small>
        <h2 style="font-size:28px;color:#b77a2e;margin-top:10px;">{{ $unitStats['vacant'] }}</h2>
    </div>

    <div class="page-card">
        <small style="color:#849087;">Maintenance</small>
        <h2 style="font-size:28px;color:#5365ad;margin-top:10px;">{{ $unitStats['maintenance'] }}</h2>
    </div>

</div>

<div class="page-card">

    <h3 style="margin-bottom:17px;">Default Rental Charges</h3>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:18px;">

        <div>
            <small style="color:#849087;">Monthly Rent</small>
            <h3 style="margin-top:6px;">
                KES {{ number_format((float)$property->default_rent, 2) }}
            </h3>
        </div>

        <div>
            <small style="color:#849087;">Security Deposit</small>
            <h3 style="margin-top:6px;">
                KES {{ number_format((float)$property->default_deposit, 2) }}
            </h3>
        </div>

        <div>
            <small style="color:#849087;">Garbage Fee</small>
            <h3 style="margin-top:6px;">
                KES {{ number_format((float)$property->default_garbage_fee, 2) }}
            </h3>
        </div>

    </div>

</div>

<div class="page-card">

    <h3 style="margin-bottom:6px;">Rental Units</h3>

    <p style="font-size:13px;color:#849087;margin-bottom:20px;">
        Manage individual houses, rent rates, deposits and occupancy.
    </p>

    <form method="GET" action="{{ route('properties.show', $property) }}"
          style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;">

        <input type="text" name="search"
               value="{{ request('search') }}"
               placeholder="Search house number..."
               class="form-control"
               style="flex:1;min-width:180px;">

        <select name="status" class="form-control" style="width:auto;">
            <option value="">All Units</option>
            <option value="vacant" {{ request('status') === 'vacant' ? 'selected' : '' }}>Vacant</option>
            <option value="occupied" {{ request('status') === 'occupied' ? 'selected' : '' }}>Occupied</option>
            <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
        </select>

        <button class="btn btn-primary">Filter</button>

        <a href="{{ route('properties.show', $property) }}" class="btn btn-secondary">Reset</a>

    </form>

    @if($units->count())

        <div class="table-wrap">

            <table class="data-table">

                <thead>
                    <tr>
                        <th>House Number</th>
                        <th>Current Tenant</th>
                        <th>Unit Type</th>
                        <th>Monthly Rent</th>
                        <th>Deposit</th>
                        <th>Garbage</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($units as $unit)

                        <tr>
                            <td><strong>{{ $unit->unit_number }}</strong></td>

                            <td>
                                @if($unit->currentTenancy?->tenant)
                                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                        <strong>{{ $unit->currentTenancy->tenant->full_name }}</strong>
                                        <a href="{{ route('tenants.show', $unit->currentTenancy->tenant) }}"
                                           class="btn btn-secondary"
                                           style="padding:7px 10px;">
                                            View
                                        </a>
                                    </div>
                                @elseif($unit->status === 'occupied')
                                    Tenant details unavailable
                                @else
                                    —
                                @endif
                            </td>

                            <td>{{ $unit->unit_type ?: 'Not specified' }}</td>

                            <td>KES {{ number_format((float)$unit->monthly_rent, 2) }}</td>

                            <td>KES {{ number_format((float)$unit->deposit_amount, 2) }}</td>

                            <td>KES {{ number_format((float)$unit->garbage_fee, 2) }}</td>

                            <td>
                                <span class="status status-{{ $unit->status }}">
                                    {{ ucfirst($unit->status) }}
                                </span>
                            </td>

                            <td>
                                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                    <a href="{{ route('units.edit', $unit) }}"
                                       class="btn btn-secondary"
                                       style="padding:8px 12px;">
                                        Edit
                                    </a>
                                    @if($unit->status === 'vacant' && $unit->tenancies->isEmpty())
                                        <form method="POST" action="{{ route('landlord-vacancies.toggle', $unit) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="btn {{ $unit->vacancyListing?->status === 'published' ? 'btn-danger' : 'btn-primary' }}"
                                                    style="padding:8px 12px;">
                                                {{ $unit->vacancyListing?->status === 'published' ? 'Unpublish' : 'Post vacancy' }}
                                            </button>
                                        </form>
                                    @elseif($unit->vacancyListing)
                                        <span class="text-xs text-gray-500">Unpublished when occupied</span>
                                    @endif
                                </div>
                            </td>
                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

        <div class="pagination">
            {{ $units->links() }}
        </div>

    @else

        <div style="text-align:center;padding:35px;color:#849087;">
            No units match your search.
        </div>

    @endif

</div>

@endsection
