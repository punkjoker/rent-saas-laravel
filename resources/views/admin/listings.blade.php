@extends('layouts.admin')

@section('title', 'Property listings')
@section('heading', 'Property Listings')
@section('description', 'Approve public advertisements or reject and revoke listings.')

@section('content')
<section class="admin-card">
    <div class="admin-table-wrap">
        <table class="admin-table" style="min-width:1100px">
            <thead>
                <tr><th>Advertisement</th><th>Landlord</th><th>Rent</th><th>Photos</th><th>Status</th><th>Moderation</th></tr>
            </thead>
            <tbody>
                @forelse($listings as $listing)
                    <tr>
                        <td>
                            <strong>{{ $listing->title }}</strong>
                            <div class="admin-muted">{{ $listing->property->property_name }} · {{ $listing->property->town }}</div>
                            @if($listing->description)<div class="admin-muted">{{ \Illuminate\Support\Str::limit($listing->description, 110) }}</div>@endif
                        </td>
                        <td>
                            {{ $listing->property->landlord->business_name ?: $listing->property->landlord->user->name }}
                            <div class="admin-muted">{{ $listing->property->landlord->user->email }}</div>
                        </td>
                        <td>KES {{ number_format((float) $listing->monthly_rent, 2) }}</td>
                        <td>
                            @if($listing->images->isNotEmpty())
                                <img src="{{ Storage::disk('public')->url($listing->images->first()->image_path) }}"
                                     alt="" style="width:82px;height:55px;object-fit:cover;border-radius:8px">
                            @else
                                <span class="admin-muted">None</span>
                            @endif
                        </td>
                        <td>
                            <span class="admin-badge admin-badge-{{ $listing->status }}">{{ $listing->status }}</span>
                            @if($listing->rejection_reason)<div class="admin-muted">{{ $listing->rejection_reason }}</div>@endif
                        </td>
                        <td>
                            <div class="admin-actions">
                                @if($listing->status !== 'approved')
                                    <form method="POST" action="{{ route('admin.listings.review', $listing) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="admin-button">Approve</button>
                                    </form>
                                @endif
                                @if($listing->status !== 'rejected')
                                    <form method="POST" action="{{ route('admin.listings.review', $listing) }}" class="admin-form">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="rejected">
                                        <input type="text" name="rejection_reason" required maxlength="2000"
                                               class="admin-input" placeholder="Reason for rejection">
                                        <button type="submit" class="admin-button admin-button-danger">Reject</button>
                                    </form>
                                @endif
                                @if($listing->status !== 'revoked')
                                    <form method="POST" action="{{ route('admin.listings.review', $listing) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="revoked">
                                        <button type="submit" class="admin-button admin-button-neutral">Revoke</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">No property advertisements have been submitted.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $listings->links() }}</div>
</section>
@endsection
