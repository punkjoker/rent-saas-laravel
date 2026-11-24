@extends('layouts.admin')

@section('title', 'Managed properties')
@section('heading', 'Managed Properties')
@section('description', 'Review the platform property inventory and deactivate or restore property records.')

@section('content')
<section class="admin-card">
    <div class="admin-table-wrap">
        <table class="admin-table" style="min-width:850px">
            <thead>
                <tr><th>Property</th><th>Landlord</th><th>Location</th><th>Units</th><th>Availability</th><th>Action</th></tr>
            </thead>
            <tbody>
                @forelse($properties as $property)
                    <tr>
                        <td><strong>{{ $property->property_name }}</strong><div class="admin-muted">{{ $property->property_type }}</div></td>
                        <td>{{ $property->landlord->business_name ?: $property->landlord->user->name }}<div class="admin-muted">{{ $property->landlord->user->email }}</div></td>
                        <td>{{ collect([$property->town, $property->county])->filter()->join(', ') ?: '—' }}</td>
                        <td>{{ number_format($property->units_count) }}</td>
                        <td><span class="admin-badge admin-badge-{{ $property->admin_revoked_at ? 'revoked' : 'approved' }}">{{ $property->admin_revoked_at ? 'revoked' : ($property->is_active ? 'active' : 'inactive') }}</span></td>
                        <td>
                            <form method="POST" action="{{ route('admin.properties.update', $property) }}"
                                  onsubmit="return confirm('{{ $property->admin_revoked_at ? 'Restore' : 'Revoke' }} this property?')">
                                @csrf @method('PATCH')
                                <input type="hidden" name="revoked" value="{{ $property->admin_revoked_at ? 0 : 1 }}">
                                <button type="submit" class="admin-button {{ !$property->admin_revoked_at ? 'admin-button-danger' : '' }}">
                                    {{ $property->admin_revoked_at ? 'Restore property' : 'Revoke property' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">No properties are registered.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $properties->links() }}</div>
</section>
@endsection
