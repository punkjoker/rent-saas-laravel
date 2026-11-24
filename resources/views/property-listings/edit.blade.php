@extends('layouts.landlord')

@section('title', 'Edit advertisement')
@section('page-heading', 'Edit Advertisement')
@section('page-description', 'Saving changes resubmits the advertisement for administrator review.')

@section('content')
<div class="page-card">
    <form method="POST" action="{{ route('property-listings.update', $listing) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('property-listings.form', ['listing' => $listing, 'properties' => collect()])
        <div class="flex gap-2 mt-6">
            <button type="submit" class="btn btn-primary">Save and resubmit</button>
            <a href="{{ route('property-listings.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>

    <div class="mt-8">
        <h2 class="text-lg font-bold mb-3">Current photos</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @forelse($listing->images as $image)
                <div class="border rounded-xl p-2">
                    <img src="{{ Storage::disk('public')->url($image->image_path) }}"
                         alt="Property photo" class="w-full h-32 object-cover rounded-lg">
                    <form method="POST" action="{{ route('property-listings.images.destroy', [$listing, $image]) }}"
                          onsubmit="return confirm('Remove this property photo?')" class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger w-full" type="submit">Remove photo</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-gray-500">No photos uploaded.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
