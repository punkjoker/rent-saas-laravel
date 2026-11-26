@extends('layouts.admin')

@section('title', 'Admin overview')
@section('heading', 'Platform Overview')
@section('description', 'Subscription income, package adoption, accounts, and advertised properties.')

@section('content')
@php($incomeMax = max(1, (float) collect($incomeSeries)->max('amount')))
@php($planMax = max(1, (int) $popularPlans->max('active_subscriptions_count')))
<section class="admin-stats">
    <article class="admin-card admin-stat"><span>Total landlords</span><strong>{{ number_format($stats['landlords']) }}</strong></article>
    <article class="admin-card admin-stat"><span>Approved landlords</span><strong>{{ number_format($stats['approved_landlords']) }}</strong></article>
    <article class="admin-card admin-stat"><span>Managed properties</span><strong>{{ number_format($stats['properties']) }}</strong></article>
    <article class="admin-card admin-stat"><span>Active subscriptions</span><strong>{{ number_format($stats['active_subscriptions']) }}</strong></article>
    <article class="admin-card admin-stat"><span>Subscription income</span><strong>KES {{ number_format($stats['income'], 0) }}</strong></article>
    <article class="admin-card admin-stat"><span>Pending accounts</span><strong>{{ number_format($stats['pending_landlords']) }}</strong></article>
    <article class="admin-card admin-stat"><span>Listings awaiting review</span><strong>{{ number_format($stats['pending_listings']) }}</strong></article>
    <article class="admin-card admin-stat"><span>Admin vacancies published</span><strong>{{ number_format($stats['published_vacancies']) }}</strong></article>
    <article class="admin-card admin-stat"><span>Accommodations published</span><strong>{{ number_format($stats['published_accommodations']) }}</strong></article>
    <article class="admin-card admin-stat"><span>Accommodation listing views</span><strong>{{ number_format($stats['accommodation_clicks']) }}</strong></article>
    <article class="admin-card admin-stat"><span>Pending accommodation bookings</span><strong>{{ number_format($stats['pending_accommodation_bookings']) }}</strong></article>
    <article class="admin-card admin-stat"><span>New contact messages</span><strong>{{ number_format($stats['new_contact_messages']) }}</strong></article>
    <article class="admin-card admin-stat"><span>Most subscribed package</span><strong style="font-size:17px">{{ $mostSubscribedPlan?->name ?? 'No plans' }}</strong></article>
</section>

<section class="admin-grid">
    <article class="admin-card">
        <h2>Subscription income</h2>
        <p class="admin-muted" style="margin:5px 0 20px">Successful payments · last 12 months</p>
        <div class="income-chart" role="img" aria-label="Monthly subscription income for the last 12 months">
            @foreach($incomeSeries as $month)
                <div class="income-column">
                    <div class="income-column-track">
                        <div class="income-column-bar" title="KES {{ number_format($month['amount'], 2) }}"
                             style="height:{{ $month['amount'] > 0 ? max(($month['amount'] / $incomeMax) * 100, 2) : 0 }}%"></div>
                    </div>
                    <span class="income-label">{{ $month['label'] }}</span>
                </div>
            @endforeach
        </div>
    </article>
    <article class="admin-card">
        <h2>Most subscribed packages</h2>
        <p class="admin-muted" style="margin:5px 0 18px">Current trial and active subscriptions</p>
        @forelse($popularPlans as $plan)
            <div style="padding:12px 0;border-bottom:1px solid #edf1ee">
                <div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:7px">
                    <span>{{ $plan->name }}</span>
                    <strong>{{ number_format($plan->active_subscriptions_count) }}</strong>
                </div>
                <div style="height:7px;overflow:hidden;border-radius:99px;background:#e8eee9">
                    <div style="height:100%;width:{{ ($plan->active_subscriptions_count / $planMax) * 100 }}%;background:linear-gradient(90deg,#73c7a0,#238060)"></div>
                </div>
            </div>
        @empty
            <p class="admin-muted">No subscription packages are configured.</p>
        @endforelse
    </article>
</section>
<section class="admin-card">
    <h2>Accommodation listing analytics</h2>
    <p class="admin-muted" style="margin:5px 0 15px">Public detail-page views and booking activity by accommodation.</p>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th>Accommodation</th><th>Landlord</th><th>Status</th><th>Views</th><th>Booking requests</th><th>Confirmed</th></tr></thead>
            <tbody>
                @forelse($topAccommodations as $stay)
                    <tr>
                        <td>{{ $stay->title }}<div class="admin-muted">{{ $stay->place_name }}</div></td>
                        <td>{{ $stay->landlord->business_name }}</td>
                        <td>{{ ucfirst($stay->status) }}</td>
                        <td>{{ number_format($stay->clicks_count) }}</td>
                        <td>{{ number_format($stay->pending_bookings_count) }}</td>
                        <td>{{ number_format($stay->confirmed_bookings_count) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">No accommodations have been listed yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
<section class="admin-card admin-actions">
    <a class="admin-button" href="{{ route('admin.accounts') }}">Review landlord accounts</a>
    <a class="admin-button" href="{{ route('admin.listings') }}">Review property listings</a>
    <a class="admin-button" href="{{ route('admin.vacancies') }}">Manage public vacancies</a>
    <a class="admin-button admin-button-neutral" href="{{ route('admin.subscriptions') }}">Manage subscriptions</a>
    <a class="admin-button admin-button-neutral" href="{{ route('admin.messages.contact') }}">Contact messages</a>
    <a class="admin-button admin-button-neutral" href="{{ route('admin.messages.support') }}">Technical support</a>
</section>
@endsection
