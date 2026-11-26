@extends('layouts.landlord')

@section('title', 'Vacancies')
@section('page-heading', 'My Vacancies')
@section('page-description', 'Publish vacant units from your properties. Occupied or reserved units are automatically unpublished.')

@section('content')
<section class="page-card">
    <form method="GET" action="{{ route('landlord-vacancies.index') }}"
          style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px">
        <input class="form-control" type="search" name="place" value="{{ $filters['place'] ?? '' }}"
               placeholder="Filter by place" style="flex:1;min-width:180px">
        <input class="form-control" type="number" name="min_price" min="0" step="1"
               value="{{ $filters['min_price'] ?? '' }}" placeholder="Minimum monthly rent" style="width:170px">
        <input class="form-control" type="number" name="max_price" min="0" step="1"
               value="{{ $filters['max_price'] ?? '' }}" placeholder="Maximum monthly rent" style="width:170px">
        <button class="btn btn-primary" type="submit">Filter</button>
        <a class="btn btn-secondary" href="{{ route('landlord-vacancies.index') }}">Reset</a>
    </form>
    <div style="overflow-x:auto;">
        <table class="data-table w-full" style="min-width:780px;">
            <thead>
                <tr>
                    <th>Property</th>
                    <th>Unit</th>
                    <th>Place</th>
                    <th>Unit type</th>
                    <th>Rent / month</th>
                    <th>Status</th>
                    <th>Public action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vacancies as $vacancy)
                    <tr>
                        <td>{{ $vacancy->property->property_name }}</td>
                        <td>{{ $vacancy->unit->unit_number ?? 'Unit removed' }}</td>
                        <td>{{ $vacancy->place_name }}</td>
                        <td>{{ $vacancy->unit_type }}</td>
                        <td>KES {{ number_format((float) $vacancy->monthly_rent, 2) }}</td>
                        <td>
                            <span class="status {{ $vacancy->status === 'published' ? 'status-active' : 'status-inactive' }}">
                                {{ ucfirst($vacancy->status) }}
                            </span>
                            @if($vacancy->unit && $vacancy->unit->status !== 'vacant')
                                <div class="text-xs text-gray-500 mt-1">Hidden because unit is {{ $vacancy->unit->status }}.</div>
                            @endif
                        </td>
                        <td>
                            @if($vacancy->unit?->status === 'vacant' &&
                                $vacancy->unit->tenancies->isEmpty())
                                <form method="POST" action="{{ route('landlord-vacancies.toggle', $vacancy->unit) }}">
                                    @csrf
                                    <button type="submit"
                                            class="btn {{ $vacancy->status === 'published' ? 'btn-danger' : 'btn-primary' }}">
                                        {{ $vacancy->status === 'published' ? 'Unpublish' : 'Publish again' }}
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-gray-500">Unavailable</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center p-6 text-gray-500">
                            No vacancy posts yet. Open a property and use “Post vacancy” on a vacant unit.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $vacancies->links() }}</div>
</section>
@endsection
