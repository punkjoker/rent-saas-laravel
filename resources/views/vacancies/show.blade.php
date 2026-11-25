@extends('layouts.public')

@section('title', $listing->title)

@section('content')
<main class="public-container">
    <a href="{{ route('home') }}#vacancies" class="public-back">← Browse vacancies</a>
    <div class="public-detail-grid">
        <div class="public-image-grid">
            @php
                $images = $listing->images->isNotEmpty()
                    ? $listing->images
                    : ($listing->property?->listing?->images ?? collect());
            @endphp
            @forelse($images as $image)
                <img src="{{ Storage::disk('public')->url($image->image_path) }}"
                     alt="{{ $listing->title }} photo"
                     class="public-listing-image">
            @empty
                <div class="public-listing-no-image">Vacancy available</div>
            @endforelse
        </div>
        <section>
            <span class="public-eyebrow">Available vacancy</span>
            <h1 class="public-title">{{ $listing->title }}</h1>
            <p class="public-muted">
                {{ $listing->place_name }}
                @if($listing->address) · {{ $listing->address }} @endif
            </p>
            <p class="public-price">
                KES {{ number_format((float) $listing->monthly_rent, 2) }} <small>/ month</small>
            </p>
            <div class="public-facts">
                <span class="public-chip">{{ $availableUnits }} {{ $availableUnits === 1 ? 'vacancy' : 'vacancies' }}</span>
                @if($listing->unit_type)<span class="public-chip">{{ $listing->unit_type }}</span>@endif
                @if($listing->bedrooms !== null)<span class="public-chip">{{ $listing->bedrooms }} bedrooms</span>@endif
                @if($listing->bathrooms !== null)<span class="public-chip">{{ $listing->bathrooms }} bathrooms</span>@endif
            </div>
            @if($listing->description)
                <div class="public-description">{{ $listing->description }}</div>
            @elseif($listing->property?->description)
                <div class="public-description">{{ $listing->property->description }}</div>
            @endif
            <div class="public-contact">
                <h2>Contact about this vacancy</h2>
                <p class="public-muted">Contact the advertiser to confirm availability and viewing details.</p>
                @if($listing->contact_phone)
                    <a href="tel:{{ $listing->contact_phone }}">Call {{ $listing->contact_phone }}</a>
                @endif
            </div>
        </section>
    </div>
</main>
@endsection
