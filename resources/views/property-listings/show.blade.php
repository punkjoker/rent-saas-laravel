@extends('layouts.public')

@section('title', $listing->title)

@section('content')
<main class="public-container">
    <a href="{{ route('home') }}#properties" class="public-back">← All advertised properties</a>
    <div class="public-detail-grid">
        <div class="public-image-grid">
            @forelse($listing->images as $image)
                <img src="{{ Storage::disk('public')->url($image->image_path) }}"
                     alt="{{ $listing->title }} photo"
                     class="public-listing-image">
            @empty
                <div class="public-listing-no-image">No photos available</div>
            @endforelse
        </div>
        <section>
            <span class="public-eyebrow">{{ $listing->property->property_type }}</span>
            <h1 class="public-title">{{ $listing->title }}</h1>
            <p class="public-muted">
                {{ $listing->property->town ?? $listing->property->county ?? '' }}
                @if($listing->property->address) · {{ $listing->property->address }} @endif
            </p>
            <p class="public-price">
                KES {{ number_format((float) $listing->monthly_rent, 2) }} <small>/ month</small>
            </p>
            <div class="public-facts">
                @if($listing->bedrooms !== null)<span class="public-chip">{{ $listing->bedrooms }} bedrooms</span>@endif
                @if($listing->bathrooms !== null)<span class="public-chip">{{ $listing->bathrooms }} bathrooms</span>@endif
                <span class="public-chip">{{ $listing->property->units->count() }} units</span>
            </div>
            @if($listing->description)
                <div class="public-description">{{ $listing->description }}</div>
            @endif
            <div class="public-contact">
                <h2>Contact about this property</h2>
                <p class="public-muted">Contact the advertiser to confirm availability and viewing details.</p>
                @if($listing->contact_phone)
                    <a href="tel:{{ $listing->contact_phone }}">Call {{ $listing->contact_phone }}</a>
                @endif
            </div>
        </section>
    </div>
</main>
@endsection
