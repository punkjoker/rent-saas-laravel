
@extends('layouts.landlord')

@section('title', 'Properties')
@section('page-heading', 'My Properties')
@section('page-description', 'Manage your apartments, rental units and property information.')

@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:25px;flex-wrap:wrap;">

    <div>
        <h2 style="font-size:19px;">Property Portfolio</h2>
        <p style="color:#849087;font-size:13px;margin-top:5px;">
            View and manage all your registered properties.
        </p>
    </div>

    <a href="{{ route('properties.create') }}" class="btn btn-primary">
        + Add New Property
    </a>

</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:17px;margin-bottom:25px;">

    <div class="page-card">
        <small style="color:#849087;">Total Properties</small>
        <h2 style="font-size:29px;margin-top:10px;">
            {{ $stats['properties'] }}
        </h2>
        <p style="font-size:12px;color:#849087;margin-top:5px;">
            Registered apartments
        </p>
    </div>

    <div class="page-card">
        <small style="color:#849087;">Total Units</small>
        <h2 style="font-size:29px;margin-top:10px;">
            {{ $stats['units'] }}
        </h2>
        <p style="font-size:12px;color:#849087;margin-top:5px;">
            All rental houses
        </p>
    </div>

    <div class="page-card">
        <small style="color:#849087;">Occupied Units</small>
        <h2 style="font-size:29px;color:#238060;margin-top:10px;">
            {{ $stats['occupied'] }}
        </h2>
        <p style="font-size:12px;color:#849087;margin-top:5px;">
            Currently occupied
        </p>
    </div>

    <div class="page-card">
        <small style="color:#849087;">Vacant Units</small>
        <h2 style="font-size:29px;color:#b77a2e;margin-top:10px;">
            {{ $stats['vacant'] }}
        </h2>
        <p style="font-size:12px;color:#849087;margin-top:5px;">
            Available for tenants
        </p>
    </div>

</div>

<div class="page-card">

    <form method="GET" action="{{ route('properties.index') }}"
          style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:22px;">

        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Search property name, code or town..."
            class="form-control"
            style="flex:1;min-width:220px;"
        >

        <input type="search" name="place" value="{{ request('place') }}"
               placeholder="Filter by town or address" class="form-control"
               style="min-width:180px;flex:1;">

        <input type="number" name="min_price" min="0" step="1" value="{{ request('min_price') }}"
               placeholder="Minimum rent" class="form-control" style="width:140px;">
        <input type="number" name="max_price" min="0" step="1" value="{{ request('max_price') }}"
               placeholder="Maximum rent" class="form-control" style="width:140px;">

        <select name="status" class="form-control" style="width:auto;min-width:140px;">
            <option value="">All statuses</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>
                Active
            </option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                Inactive
            </option>
        </select>

        <button type="submit" class="btn btn-primary">
            Search
        </button>

        <a href="{{ route('properties.index') }}" class="btn btn-secondary">
            Reset
        </a>

    </form>

    @if($properties->count())

        <div class="table-wrap">

            <table class="data-table">

                <thead>
                    <tr>
                        <th>Property</th>
                        <th>Location</th>
                        <th>Total Units</th>
                        <th>Occupancy</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($properties as $property)

                        @php
                            $total = $property->units_count;
                            $occupied = $property->occupied_units_count;
                            $percentage = $total > 0
                                ? round(($occupied / $total) * 100)
                                : 0;
                        @endphp

                        <tr>

                            <td>
                                <strong>{{ $property->property_name }}</strong>

                                <div style="font-size:11px;color:#849087;margin-top:4px;">
                                    {{ $property->property_code ?: 'No property code' }}
                                </div>
                            </td>

                            <td>
                                {{ $property->town ?: 'Not specified' }},
                                {{ $property->county ?: '-' }}
                            </td>

                            <td>
                                <strong>{{ $total }}</strong>
                            </td>

                            <td style="min-width:130px;">

                                <div style="font-size:12px;margin-bottom:7px;">
                                    {{ $occupied }} / {{ $total }} occupied
                                </div>

                                <div style="height:6px;background:#e8eee9;border-radius:10px;overflow:hidden;">
                                    <div style="height:100%;width:{{ $percentage }}%;background:#238060;border-radius:10px;"></div>
                                </div>

                                <small style="color:#849087;">
                                    {{ $percentage }}%
                                </small>

                            </td>

                            <td>
                                <span class="status {{ $property->is_active ? 'status-active' : 'status-inactive' }}">
                                    {{ $property->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td>
                                <div style="display:flex;gap:7px;flex-wrap:wrap;">

                                    <a href="{{ route('properties.show', $property) }}"
                                       class="btn btn-primary"
                                       style="padding:8px 11px;">
                                        View
                                    </a>

                                    <a href="{{ route('properties.edit', $property) }}"
                                       class="btn btn-secondary"
                                       style="padding:8px 11px;">
                                        Edit
                                    </a>

                                </div>
                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

        <div class="pagination">
            {{ $properties->links() }}
        </div>

    @else

        <div style="text-align:center;padding:55px 20px;">

            <div style="font-size:42px;margin-bottom:15px;">🏢</div>

            <h3 style="font-size:18px;margin-bottom:8px;">
                No properties found
            </h3>

            <p style="font-size:13px;color:#849087;margin-bottom:20px;">
                You haven't registered any properties yet, or no results match your search.
            </p>

            <a href="{{ route('properties.create') }}" class="btn btn-primary">
                Register Your First Property
            </a>

        </div>

    @endif

</div>

@endsection
