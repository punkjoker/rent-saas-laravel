@extends('layouts.landlord')

@section('title', 'Accommodation bookings')
@section('page-heading', 'Accommodation Booking Requests')
@section('page-description', 'Review guest dates and contact details, then confirm or decline each request.')

@section('content')
<section class="page-card">
    <div class="table-wrap">
        <table class="data-table" style="min-width:1000px">
            <thead><tr><th>Accommodation</th><th>Guest</th><th>Dates</th><th>Party</th><th>Contact / message</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
                @forelse($bookings as $booking)
                    <tr>
                        <td>{{ $booking->accommodation->title }}</td>
                        <td>{{ $booking->guest_name }}</td>
                        <td>{{ $booking->check_in->format('d M Y') }} – {{ $booking->check_out->format('d M Y') }}</td>
                        <td>{{ $booking->guests }} guests</td>
                        <td>
                            <a href="mailto:{{ $booking->guest_email }}">{{ $booking->guest_email }}</a><br>
                            <a href="tel:{{ $booking->guest_phone }}">{{ $booking->guest_phone }}</a>
                            @if($booking->guest_message)<p>{{ $booking->guest_message }}</p>@endif
                        </td>
                        <td><span class="status {{ $booking->status === 'confirmed' ? 'status-active' : 'status-inactive' }}">{{ ucfirst($booking->status) }}</span></td>
                        <td>
                            @if($booking->status === 'pending')
                                <div style="display:flex;gap:6px">
                                    @foreach(['confirmed' => 'Confirm', 'declined' => 'Decline'] as $status => $label)
                                        <form method="POST" action="{{ route('accommodations.bookings.update', $booking) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $status }}">
                                            <button class="btn {{ $status === 'confirmed' ? 'btn-primary' : 'btn-danger' }}" type="submit">{{ $label }}</button>
                                        </form>
                                    @endforeach
                                </div>
                            @else
                                {{ $booking->responded_at?->format('d M Y H:i') }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center;padding:30px">No booking requests yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $bookings->links() }}
</section>
@endsection
