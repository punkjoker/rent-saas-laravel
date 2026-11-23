@extends('layouts.landlord')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Vacated Tenancies</h1>
            <p class="text-gray-500 mt-1">Review archived tenancy records and reconcile deposits.</p>
        </div>
        <a href="{{ route('tenancies.index') }}" class="btn bg-gray-200 text-gray-700">
            Active Tenancies
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="page-card">
        <form method="GET" action="{{ route('tenancies.vacated') }}" class="flex flex-col sm:flex-row gap-3">
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Search tenant, property or unit..."
                   class="form-control flex-1">
            <button type="submit" class="btn">Search</button>
            @if(request('search'))
                <a href="{{ route('tenancies.vacated') }}" class="btn bg-gray-200 text-gray-700 text-center">
                    Reset
                </a>
            @endif
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
                        <th>Vacated On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenancies as $tenancy)
                        <tr>
                            <td>
                                <div class="font-semibold">{{ $tenancy->tenant->full_name }}</div>
                                <div class="text-xs text-gray-500">{{ $tenancy->tenant->phone }}</div>
                            </td>
                            <td>{{ $tenancy->property->property_name }}</td>
                            <td>{{ $tenancy->unit->unit_number }}</td>
                            <td>KES {{ number_format((float) $tenancy->monthly_rent, 2) }}</td>
                            <td>{{ $tenancy->ended_at?->format('d M Y') ?? '—' }}</td>
                            <td>
                                <div class="vacated-actions">
                                    <a href="{{ route('tenancies.reconciliation', $tenancy->id) }}"
                                       class="btn btn-primary">
                                        Reconcile
                                    </a>
                                    <a href="{{ route('tenancies.reconciliation.final', $tenancy->id) }}"
                                       class="btn btn-secondary">
                                        Final View
                                    </a>
                                    <a href="{{ route('tenancies.show', $tenancy->id) }}"
                                       class="btn btn-outline">
                                        Record
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-500">
                                @if(request('search'))
                                    No vacated tenancies match your search.
                                @else
                                    No vacated tenancies found.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $tenancies->links() }}</div>
    </div>
</div>
@endsection
