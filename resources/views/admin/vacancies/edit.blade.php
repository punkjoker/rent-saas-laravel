@extends('layouts.admin')

@section('title', 'Edit vacancy')
@section('heading', 'Edit Vacancy')
@section('description', 'Update the standalone vacancy details and photos.')

@section('content')
<section class="admin-card">
    <form method="POST" action="{{ route('admin.vacancies.update', $vacancy) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('admin.vacancies.form', ['vacancy' => $vacancy])
        <div class="admin-actions" style="margin-top:18px">
            <button type="submit" class="admin-button">Save changes</button>
            <a href="{{ route('admin.vacancies') }}" class="admin-button admin-button-neutral">Cancel</a>
        </div>
    </form>
    <div class="admin-grid" style="margin-top:24px">
        @forelse($vacancy->images as $image)
            <div>
                <img src="{{ Storage::disk('public')->url($image->image_path) }}" alt="Vacancy photo"
                     style="width:100%;height:160px;object-fit:cover;border-radius:10px">
                <form method="POST" action="{{ route('admin.vacancies.images.destroy', [$vacancy, $image]) }}"
                      style="margin-top:7px" onsubmit="return confirm('Remove this photo?')">
                    @csrf @method('DELETE')
                    <button class="admin-button admin-button-danger" type="submit">Remove photo</button>
                </form>
            </div>
        @empty
            <p class="admin-muted">No photos added.</p>
        @endforelse
    </div>
</section>
@endsection
