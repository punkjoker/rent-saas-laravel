@extends('layouts.admin')

@section('title', 'Upload vacancy')
@section('heading', 'Upload a Vacancy')
@section('description', 'Admin-created vacancy posts publish immediately to the homepage.')

@section('content')
<section class="admin-card">
    <form method="POST" action="{{ route('admin.vacancies.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.vacancies.form', ['vacancy' => null])
        <div class="admin-actions" style="margin-top:18px">
            <button type="submit" class="admin-button">Publish vacancy</button>
            <a href="{{ route('admin.vacancies') }}" class="admin-button admin-button-neutral">Cancel</a>
        </div>
    </form>
</section>
@endsection
