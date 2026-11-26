@extends('layouts.landlord')

@section('title', 'Edit accommodation')
@section('page-heading', 'Edit Accommodation')
@section('page-description', 'Update the listing information and add more photos.')

@section('content')
<section class="page-card">
    <form method="POST" action="{{ route('accommodations.update', $accommodation) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('accommodations.form', ['accommodation' => $accommodation, 'properties' => $properties])
        <div style="margin-top:16px"><button type="submit" class="btn btn-primary">Save changes</button></div>
    </form>
    <h2 style="margin:25px 0 12px">Current photos</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px">
        @forelse($accommodation->images as $image)
            <div>
                <img src="{{ Storage::disk('public')->url($image->image_path) }}" alt="Accommodation photo"
                     style="width:100%;height:140px;object-fit:cover;border-radius:10px">
                <form method="POST" action="{{ route('accommodations.images.destroy', [$accommodation, $image]) }}" style="margin-top:6px">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger" type="submit">Remove photo</button>
                </form>
            </div>
        @empty
            <p>No photos uploaded yet.</p>
        @endforelse
    </div>
</section>
@endsection
