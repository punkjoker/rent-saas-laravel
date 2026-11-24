@extends('layouts.landlord')

@section('title', 'Advertise a property')
@section('page-heading', 'Advertise a Property')
@section('page-description', 'Create an advertisement and submit it for review.')

@section('content')
<div class="page-card">
    @if($properties->isEmpty())
        <p class="text-gray-600">All your properties already have advertisements. You can edit an existing listing from the advertisements page.</p>
        <a href="{{ route('property-listings.index') }}" class="btn btn-secondary mt-4">Back to advertisements</a>
    @else
        <form method="POST" action="{{ route('property-listings.store') }}" enctype="multipart/form-data">
            @csrf
            @include('property-listings.form', ['listing' => null, 'properties' => $properties])
            <div class="flex gap-2 mt-6">
                <button type="submit" class="btn btn-primary">Submit for approval</button>
                <a href="{{ route('property-listings.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    @endif
</div>
@endsection
