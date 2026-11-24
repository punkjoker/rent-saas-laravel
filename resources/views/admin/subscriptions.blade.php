@extends('layouts.admin')

@section('title', 'Subscriptions')
@section('heading', 'Subscriptions')
@section('description', 'Review landlord plans and grant audited subscription extensions.')

@section('content')
<section class="admin-card">
    <div class="admin-table-wrap">
        <table class="admin-table" style="min-width:1050px">
            <thead>
                <tr>
                    <th>Landlord</th>
                    <th>Properties</th>
                    <th>Current package</th>
                    <th>Status</th>
                    <th>Expires</th>
                    <th>Extend subscription</th>
                </tr>
            </thead>
            <tbody>
                @forelse($landlords as $landlord)
                    @php($subscription = $landlord->subscriptions->first())
                    <tr>
                        <td>
                            <strong>{{ $landlord->business_name ?: $landlord->user->name }}</strong>
                            <div class="admin-muted">{{ $landlord->user->email }}</div>
                        </td>
                        <td>{{ number_format($landlord->properties_count) }}</td>
                        <td>{{ $subscription?->plan?->name ?? $subscription?->selectedPlan?->name ?? '—' }}</td>
                        <td><span class="admin-badge admin-badge-{{ $subscription?->status === 'active' ? 'approved' : 'pending' }}">{{ $subscription?->status ?? 'none' }}</span></td>
                        <td>
                            {{ ($subscription?->ends_at ?? $subscription?->trial_ends_at)?->format('d M Y') ?? '—' }}
                        </td>
                        <td>
                            @if($subscription)
                                <form method="POST" action="{{ route('admin.subscriptions.extend', $landlord) }}" class="admin-form">
                                    @csrf
                                    <input type="number" name="days" min="1" max="3650" required class="admin-input" placeholder="Days to add">
                                    <input type="text" name="reason" maxlength="500" required class="admin-input" placeholder="Reason for extension">
                                    <button type="submit" class="admin-button">Grant extension</button>
                                </form>
                            @else
                                <span class="admin-muted">No subscription to extend.</span>
                            @endif
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
