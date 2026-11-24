@extends('layouts.admin')

@section('title', 'Landlord accounts')
@section('heading', 'Landlord Accounts')
@section('description', 'Approve or reject landlord accounts. Rejected accounts are blocked from the landlord workspace.')

@section('content')
<section class="admin-card">
    <div class="admin-table-wrap">
        <table class="admin-table" style="min-width:1000px">
            <thead>
                <tr><th>Landlord</th><th>Status</th><th>Properties</th><th>Tenants</th><th>Reason</th><th>Moderation</th></tr>
            </thead>
            <tbody>
                @forelse($landlords as $landlord)
                    <tr>
                        <td>
                            <strong>{{ $landlord->business_name ?: $landlord->user->name }}</strong>
                            <div class="admin-muted">{{ $landlord->user->email }}</div>
                            <div class="admin-muted">{{ $landlord->phone }}</div>
                        </td>
                        <td><span class="admin-badge admin-badge-{{ $landlord->approval_status }}">{{ $landlord->approval_status }}</span></td>
                        <td>{{ number_format($landlord->properties_count) }}</td>
                        <td>{{ number_format($landlord->tenants_count) }}</td>
                        <td>{{ $landlord->rejection_reason ?: '—' }}</td>
                        <td>
                            <div class="admin-actions">
                                @if($landlord->approval_status !== 'approved')
                                    <form method="POST" action="{{ route('admin.accounts.update', $landlord) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="approval_status" value="approved">
                                        <button class="admin-button" type="submit">Approve</button>
                                    </form>
                                @endif
                                @if($landlord->approval_status !== 'pending')
                                    <form method="POST" action="{{ route('admin.accounts.update', $landlord) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="approval_status" value="pending">
                                        <button class="admin-button admin-button-neutral" type="submit">Set pending</button>
                                    </form>
                                @endif
                                @if($landlord->approval_status !== 'rejected')
                                    <form method="POST" action="{{ route('admin.accounts.update', $landlord) }}" class="admin-form">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="approval_status" value="rejected">
                                        <input type="text" name="rejection_reason" required maxlength="2000"
                                               class="admin-input" placeholder="Reason for rejection">
                                        <button class="admin-button admin-button-danger" type="submit">Reject account</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">No landlord accounts found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $landlords->links() }}</div>
</section>
@endsection
