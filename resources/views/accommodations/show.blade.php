@extends('layouts.public')

@section('title', $accommodation->title)

@section('content')
<main class="public-container">
    <a href="{{ route('home') }}#accommodations" class="public-back">← Browse accommodations</a>
    <div class="public-detail-grid">
        <div class="public-image-grid">
            @forelse($accommodation->images as $image)
                <img src="{{ Storage::disk('public')->url($image->image_path) }}"
                     alt="{{ $accommodation->title }} photo" class="public-listing-image">
            @empty
                <div class="public-listing-no-image">Accommodation photos coming soon</div>
            @endforelse
        </div>
        <section>
            <span class="public-eyebrow">Accommodation</span>
            <h1 class="public-title">{{ $accommodation->title }}</h1>
            <p class="public-muted">{{ $accommodation->place_name }} @if($accommodation->address) · {{ $accommodation->address }} @endif</p>
            <p class="public-price">KES {{ number_format((float) $accommodation->price_per_night, 2) }} <small>/ night</small></p>
            <div class="public-facts">
                <span class="public-chip">Up to {{ $accommodation->max_guests }} guests</span>
                @if($accommodation->bedrooms !== null)<span class="public-chip">{{ $accommodation->bedrooms }} bedrooms</span>@endif
                @if($accommodation->bathrooms !== null)<span class="public-chip">{{ $accommodation->bathrooms }} bathrooms</span>@endif
            </div>
            @if($accommodation->description)<div class="public-description">{{ $accommodation->description }}</div>@endif

            <div class="public-contact">
                <h2>Request a booking</h2>
                <p class="public-muted">The host will confirm or decline your request. No payment is taken on this page.</p>
                @if(session('success'))<p style="margin:12px 0;color:#176b55">{{ session('success') }}</p>@endif
                @if($errors->any())
                    <div class="admin-error" style="margin-top:12px">
                        @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                    </div>
                @endif
                <form method="POST" action="{{ route('accommodations.public.book', $accommodation) }}"
                      style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-top:14px">
                    @csrf
                    <input class="admin-input" name="guest_name" placeholder="Your name" required maxlength="150" value="{{ old('guest_name') }}">
                    <input class="admin-input" type="email" name="guest_email" placeholder="Email" required maxlength="255" value="{{ old('guest_email') }}">
                    <input class="admin-input" type="tel" name="guest_phone" placeholder="Phone" required maxlength="30" value="{{ old('guest_phone') }}">
                    <input class="admin-input" type="number" name="guests" min="1" max="{{ $accommodation->max_guests }}" required value="{{ old('guests', 1) }}" aria-label="Number of guests">
                    <label class="public-muted">Check-in
                        <input class="admin-input" type="date" name="check_in" min="{{ now()->toDateString() }}" required value="{{ old('check_in') }}">
                    </label>
                    <label class="public-muted">Check-out
                        <input class="admin-input" type="date" name="check_out" required value="{{ old('check_out') }}">
                    </label>
                    <textarea class="admin-input" name="guest_message" rows="3" maxlength="2000"
                              placeholder="Message for the host" style="grid-column:1/-1">{{ old('guest_message') }}</textarea>
                    <button class="admin-button" type="submit" style="grid-column:1/-1">Send booking request</button>
                </form>
            </div>
        </section>
    </div>
</main>
@endsection
