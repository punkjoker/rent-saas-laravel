@extends('layouts.landlord')

@section('title', 'Add accommodation')
@section('page-heading', 'Add Accommodation')
@section('page-description', 'Create a short-stay listing. You can publish it after saving.')

@section('content')
<section class="page-card">
    <form method="POST" action="{{ route('accommodations.store') }}" enctype="multipart/form-data">
        @csrf
        @include('accommodations.form', ['accommodation' => null, 'properties' => $properties])
        <div style="display:flex;gap:8px;margin-top:18px">
            <button type="submit" class="btn btn-primary">Save draft</button>
            <a href="{{ route('accommodations.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</section>
@endsection
