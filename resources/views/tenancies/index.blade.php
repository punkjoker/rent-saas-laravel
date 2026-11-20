
@extends('layouts.landlord')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Tenancy Management</h1>
            <p class="text-gray-500 mt-1">Manage tenant agreements and unit allocations.</p>
        </div>

        <a href="{{ route('tenancies.create') }}"
           class="btn inline-flex items-center justify-center">
            + Create Tenancy
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="page-card">
            <p class="text-sm text-gray-500">Active Tenancies</p>
            <p class="text-3xl font-bold text-green-700 mt-2">{{ $stats['active'] }}</p>
        </div>

        <div class="page-card">
            <p class="text-sm text-gray-500">Upcoming Tenancies</p>
            <p class="text-3xl font-bold text-blue-700 mt-2">{{ $stats['upcoming'] }}</p>
        </div>

        <div class="page-card">
            <p class="text-sm text-gray-500">Ended Tenancies</p>
            <p class="text-3xl font-bold text-gray-600 mt-2">{{ $stats['ended'] }}</p>
        </div>
    </div>

    <div class="page-card">
        <form method="GET" action="{{ route('tenancies.index') }}"
              class="grid grid-cols-1 md:grid-cols-4 gap-3">

            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Search tenant, property or unit..."
                   class="form-control md:col-span-2">

            <select name="status" class="form-control">
                <option value="">All statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="upcoming" @selected(request('status') === 'upcoming')>Upcoming</option>
                <option value="ended" @selected(request('status') === 'ended')>Ended</option>
            </select>

            <div class="flex gap-2">
                <button type="submit" class="btn flex-1">Search</button>
                <a href="{{ route('tenancies.index') }}"
                   class="btn bg-gray-200 text-gray-700 flex-1 text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="page-card">
        <div class="overflow-x-auto">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th>Tenant</th>
                        <th>Property</th>
                        <th>Unit</th>
                        <th>Monthly Rent</th>
                        <th>Start Date</th>
                        <th>Lease End</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($tenancies as $tenancy)
                        <tr>
                            <td>
                                <div class="font-semibold">
                                    {{ $tenancy->tenant->first_name }}
                                    {{ $tenancy->tenant->middle_name }}
                                    {{ $tenancy->tenant->last_name }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $tenancy->tenant->phone }}
                                </div>
                            </td>

                            <td>{{ $tenancy->property->property_name }}</td>
                            <td>{{ $tenancy->unit->unit_number }}</td>

                            <td>
                                KES {{ number_format((float) $tenancy->monthly_rent, 2) }}
                            </td>

                            <td>{{ $tenancy->start_date->format('d M Y') }}</td>
                            <td>{{ $tenancy->lease_end_date?->format('d M Y') ?? 'Open-ended' }}</td>

                            <td>
                                @if($tenancy->status === 'active')
                                    <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-700">
                                        Active
                                    </span>
                                @elseif($tenancy->status === 'upcoming')
                                    <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-700">
                                        Upcoming
                                    </span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-600">
                                        Ended
                                    </span>
                                @endif
                            </td>

                            <td>
                                <a href="{{ route('tenancies.show', $tenancy->id) }}"
                                   class="text-green-700 font-semibold hover:underline">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-gray-500">
                                No tenancies found. Create your first tenancy to allocate a unit.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $tenancies->links() }}
        </div>
    </div>

</div>
@endsection
