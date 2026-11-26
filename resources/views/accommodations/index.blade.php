@extends('layouts.landlord')

@section('title', 'Accommodations')
@section('page-heading', 'Accommodations')
@section('page-description', 'Publish short-stay listings, manage availability and review guest booking requests.')

@section('content')
<section class="page-card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:18px">
        <p>Publish each listing when its details and photos are ready.</p>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <a href="{{ route('accommodations.bookings') }}" class="btn btn-secondary">Booking requests</a>
            <a href="{{ route('accommodations.create') }}" class="btn btn-primary">Add accommodation</a>
        </div>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr><th>Stay</th><th>Place</th><th>Nightly rate</th><th>Guests</th><th>Photos</th><th>Views</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($accommodations as $stay)
                    <tr>
                        <td><strong>{{ $stay->title }}</strong></td>
                        <td>{{ $stay->place_name }}</td>
                        <td>KES {{ number_format((float) $stay->price_per_night, 2) }}</td>
                        <td>{{ $stay->max_guests }}</td>
                        <td>{{ $stay->images_count }}</td>
                        <td>{{ number_format($stay->clicks_count) }}</td>
                        <td><span class="status {{ $stay->status === 'published' ? 'status-active' : 'status-inactive' }}">{{ ucfirst($stay->status) }}</span></td>
                        <td>
                            <div style="display:flex;gap:6px;flex-wrap:wrap">
                                <a class="btn btn-secondary" href="{{ route('accommodations.edit', $stay) }}">Edit</a>
                                @if($stay->status === 'published')
                                    <a class="btn btn-secondary" href="{{ route('accommodations.public.show', $stay) }}" target="_blank" rel="noopener">View</a>
                                @endif
                                <form method="POST" action="{{ route('accommodations.toggle', $stay) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn {{ $stay->status === 'published' ? 'btn-danger' : 'btn-primary' }}" type="submit">
                                        {{ $stay->status === 'published' ? 'Unpublish' : 'Publish' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="text-align:center;padding:30px">No accommodations listed yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $accommodations->links() }}
</section>
@endsection
