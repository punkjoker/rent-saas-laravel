@extends('layouts.landlord')

@section('title', 'Rent Payments')
@section('page-heading', 'Rent Payments')
@section('page-description', 'Review tenant payment reports, record payments, and monitor arrears.')

@push('styles')
<style>
    .payment-report-table th,
    .payment-report-table td {
        padding: 8px 10px;
        vertical-align: middle;
    }

    .payment-report-actions {
        display: flex;
        flex-flow: row nowrap;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
    }

    .payment-report-actions form {
        margin: 0;
    }

    .payment-report-actions .btn {
        padding: 6px 8px;
        font-size: 12px;
        line-height: 1.2;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if(session('share_url'))
        <section class="page-card">
            <h2 class="font-bold text-lg">Tenant payment link</h2>
            <p class="text-sm text-gray-600 mt-1">
                Copy and share this private link. It gives access to rent history and receipts, and can be revoked at any time.
            </p>
            <div class="flex flex-col sm:flex-row gap-2 mt-3">
                <input id="shareUrl" class="form-control flex-1" readonly value="{{ session('share_url') }}">
                <button type="button" class="btn btn-primary" onclick="navigator.clipboard.writeText(document.getElementById('shareUrl').value)">
                    Copy link
                </button>
            </div>
        </section>
    @endif

    <section class="page-card">
        <form method="GET" action="{{ route('payments.index') }}" class="flex flex-col lg:flex-row lg:items-end gap-3">
            <label class="block flex-1 min-w-0">
                <span class="text-sm font-semibold">Report month</span>
                <input type="month" name="month" value="{{ $month }}" class="form-control mt-1">
            </label>
            <label class="block flex-1 min-w-0">
                <span class="text-sm font-semibold">Property</span>
                <select name="property_id" class="form-control mt-1">
                    <option value="">All properties</option>
                    @foreach($properties as $property)
                        <option value="{{ $property->id }}" @selected($propertyId === $property->id)>
                            {{ $property->property_name }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label class="block flex-[1.5] min-w-0">
                <span class="text-sm font-semibold">Tenant, house, or property</span>
                <input type="search" name="search" value="{{ $search }}" class="form-control mt-1" placeholder="Search">
            </label>
            <button type="submit" class="btn btn-primary lg:flex-none">View report</button>
        </form>
    </section>

    <section class="page-card">
        <h2 class="font-bold text-lg mb-4">Payment reports awaiting review ({{ $pendingSubmissions->count() }})</h2>
        @forelse($pendingSubmissions as $submission)
            <div class="border rounded-lg p-4 mb-3">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <div class="space-y-1">
                        <p class="font-semibold">
                            {{ $submission->tenancy->tenant->full_name }}
                            · {{ $submission->tenancy->property->property_name }}
                            · House {{ $submission->tenancy->unit->unit_number }}
                        </p>
                        <p class="text-sm text-gray-600">
                            Reported {{ $submission->transaction_date->format('d M Y, h:i A') }}
                            · KES {{ number_format((float) $submission->amount, 2) }}
                            · Code: {{ $submission->transaction_code }}
                        </p>
                        @if($submission->message)
                            <p class="text-sm text-gray-700">{{ $submission->message }}</p>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('payments.submissions.approve', $submission->id) }}">
                            @csrf
                            <button type="submit" class="btn bg-green-700 text-white">Approve payment</button>
                        </form>
                        <form method="POST" action="{{ route('payments.submissions.reject', $submission->id) }}" class="flex gap-2">
                            @csrf
                            <input name="review_message" class="form-control" maxlength="2000" placeholder="Reason (optional)">
                            <button type="submit" class="btn bg-red-700 text-white">Reject</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-gray-500">There are no tenant payment reports awaiting review.</p>
        @endforelse
    </section>

    <section class="page-card">
        <h2 class="font-bold text-lg">Monthly rent and arrears</h2>
        <p class="text-sm text-gray-600 mt-1 mb-4">
            Approved payments are applied to the oldest outstanding rent charges first, including earlier months.
        </p>

        <div class="overflow-x-auto">
            <table class="data-table payment-report-table w-full min-w-[1200px]">
                <thead>
                    <tr>
                        <th>Property</th>
                        <th>House</th>
                        <th>Tenant</th>
                        <th class="text-right">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y') }} due</th>
                        <th class="text-right">Billed to month</th>
                        <th class="text-right">Paid toward charges</th>
                        <th class="text-right">Arrears</th>
                        <th class="text-right">Credit</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        @php
                            $tenancy = $report['tenancy'];
                            $summary = $report['summary'];
                        @endphp
                        <tr>
                            <td>{{ $tenancy->property->property_name }}</td>
                            <td>{{ $tenancy->unit->unit_number }}</td>
                            <td>{{ $tenancy->tenant->full_name }}</td>
                            <td class="text-right">KES {{ number_format($report['selected_month_due'], 2) }}</td>
                            <td class="text-right">KES {{ number_format($summary['total_due'], 2) }}</td>
                            <td class="text-right">KES {{ number_format($summary['total_paid'], 2) }}</td>
                            <td class="text-right font-bold">KES {{ number_format($summary['arrears'], 2) }}</td>
                            <td class="text-right">KES {{ number_format($summary['credit'], 2) }}</td>
                            <td>
                                <div class="payment-report-actions">
                                    <a href="{{ route('payments.history', ['tenancy' => $tenancy->id, 'month' => $month]) }}"
                                       class="btn btn-secondary">
                                        History
                                    </a>
                                    <a href="{{ route('payments.create', $tenancy->id) }}" class="btn btn-primary">
                                        Record
                                    </a>
                                    @if($tenancy->payment_share_token_hash)
                                        <form method="POST" action="{{ route('payments.share', $tenancy->id) }}" onsubmit="return confirm('Create a new link and invalidate the existing tenant link?');">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary">Rotate link</button>
                                        </form>
                                        <form method="POST" action="{{ route('payments.share.revoke', $tenancy->id) }}" onsubmit="return confirm('Revoke the tenant payment link?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn bg-red-700 text-white">Revoke</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('payments.share', $tenancy->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary">Share link</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-gray-500 p-6">No tenancies were found for this report.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
