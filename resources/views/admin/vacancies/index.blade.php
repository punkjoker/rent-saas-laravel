@extends('layouts.admin')

@section('title', 'Vacancies')
@section('heading', 'Public Vacancies')
@section('description', 'Create standalone vacancy advertisements, upload photos, and manage publication.')

@section('content')
<section class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:16px">
        <div>
            <h2>Admin-created vacancies</h2>
            <p class="admin-muted" style="margin-top:5px">These posts are independent of landlord properties.</p>
        </div>
        <a href="{{ route('admin.vacancies.create') }}" class="admin-button">Upload vacancy</a>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table" style="min-width:1000px">
            <thead>
                <tr><th>Vacancy</th><th>Place</th><th>Rent</th><th>Photos</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($vacancies as $vacancy)
                    <tr>
                        <td>
                            <strong>{{ $vacancy->title }}</strong>
                            <div class="admin-muted">{{ $vacancy->unit_type ?: 'Unit type not specified' }}</div>
                        </td>
                        <td>{{ $vacancy->place_name }}<div class="admin-muted">{{ $vacancy->address }}</div></td>
                        <td>KES {{ number_format((float) $vacancy->monthly_rent, 2) }}</td>
                        <td>{{ $vacancy->images->count() }}</td>
                        <td><span class="admin-badge admin-badge-{{ $vacancy->status === 'published' ? 'approved' : 'revoked' }}">{{ $vacancy->status }}</span></td>
                        <td>
                            <div class="admin-actions">
                                <a class="admin-button admin-button-neutral" href="{{ route('admin.vacancies.edit', $vacancy) }}">Edit</a>
                                <a class="admin-button admin-button-neutral" target="_blank" rel="noopener" href="{{ route('vacancies.show', $vacancy) }}">View</a>
                                <form method="POST" action="{{ route('admin.vacancies.toggle', $vacancy) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="admin-button">{{ $vacancy->status === 'published' ? 'Unpublish' : 'Publish' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.vacancies.destroy', $vacancy) }}"
                                      onsubmit="return confirm('Delete this vacancy and its photos?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="admin-button admin-button-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">No admin-created vacancies. Upload one to publish it on the homepage.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $vacancies->links() }}</div>
</section>
@endsection
