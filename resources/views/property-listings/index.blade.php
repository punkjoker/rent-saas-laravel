@extends('layouts.landlord')

@section('title', 'Property advertisements')
@section('page-heading', 'Property Advertisements')
@section('page-description', 'Submit property details and photos for public listing approval.')

@section('content')
<div class="page-card">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
        <div>
            <h2 class="text-lg font-bold">Your advertisements</h2>
            <p class="text-sm text-gray-500 mt-1">Only administrator-approved properties appear on the public homepage.</p>
        </div>
        <a href="{{ route('property-listings.create') }}" class="btn btn-primary">Advertise a property</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="overflow-x-auto">
        <table class="data-table w-full min-w-[760px]">
            <thead>
                <tr>
                    <th>Listing</th>
                    <th>Property</th>
                    <th>Rent / month</th>
                    <th>Status</th>
                    <th>Photos</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($listings as $listing)
                    <tr>
                        <td class="font-semibold">{{ $listing->title }}</td>
                        <td>{{ $listing->property->property_name }}</td>
                        <td>KES {{ number_format((float) $listing->monthly_rent, 2) }}</td>
                        <td>
                            <span class="font-semibold {{ $listing->status === 'approved' ? 'text-green-700' : ($listing->status === 'rejected' || $listing->status === 'revoked' ? 'text-red-700' : 'text-amber-700') }}">
                                {{ ucfirst($listing->status) }}
                            </span>
                            @if($listing->rejection_reason)
                                <div class="text-xs text-gray-500 mt-1">{{ $listing->rejection_reason }}</div>
                            @endif
                        </td>
                        <td>{{ $listing->images->count() }}</td>
                        <td>
                            <a href="{{ route('property-listings.edit', $listing) }}" class="btn btn-secondary">Edit</a>
                            @if($listing->status === 'approved')
                                <a href="{{ route('rentals.show', $listing->slug) }}" class="btn btn-outline" target="_blank" rel="noopener">View public</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center p-6 text-gray-500">No property advertisements have been submitted.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $listings->links() }}</div>
</div>
@endsection
