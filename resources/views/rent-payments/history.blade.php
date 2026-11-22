@extends('layouts.landlord')

@section('title', 'Rent History')
@section('page-heading', 'Rent History')
@section('page-description', 'Review monthly rent charges, approved payments, and outstanding balances.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold">{{ $tenant->full_name }}</h2>
            <p class="text-sm text-gray-500">Payment history for {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}.</p>
        </div>
        <a href="{{ $backUrl }}" class="btn btn-secondary">{{ $backLabel }}</a>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <section class="page-card">
        <form method="GET" action="{{ $filterAction }}" class="flex flex-col sm:flex-row sm:items-end gap-3">
            <label class="block sm:flex-1">
                <span class="text-sm font-semibold">Report month</span>
                <input type="month" name="month" value="{{ $month }}" class="form-control mt-1">
            </label>
            <button type="submit" class="btn btn-primary">Update history</button>
        </form>
    </section>

    @forelse($histories as $history)
        @php
            $tenancy = $history['tenancy'];
            $summary = $history['summary'];
        @endphp
        <section class="page-card space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold">{{ $tenancy->property->property_name }} · House {{ $tenancy->unit->unit_number }}</h2>
                    <p class="text-sm text-gray-500">
                        {{ ucfirst($tenancy->status) }} tenancy · Started {{ $tenancy->start_date->format('d M Y') }}
                        @if($tenancy->ended_at) · Ended {{ $tenancy->ended_at->format('d M Y') }} @endif
                    </p>
                </div>
                <a href="{{ route('payments.create', $tenancy->id) }}" class="btn btn-primary">Record payment</a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total collected</p>
                    <p class="text-xl font-bold mt-1">KES {{ number_format($history['total_collected'], 2) }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Arrears through last month</p>
                    <p class="text-xl font-bold mt-1">KES {{ number_format($history['last_month_arrears'], 2) }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Arrears through {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</p>
                    <p class="text-xl font-bold mt-1">KES {{ number_format($history['this_month_arrears'], 2) }}</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">Available credit</p>
                    <p class="text-xl font-bold mt-1">KES {{ number_format($summary['credit'], 2) }}</p>
                </div>
            </div>

            <div>
                <h3 class="font-bold mb-3">Rent breakdowns</h3>
                <div class="overflow-x-auto">
                    <table class="data-table w-full min-w-[850px]">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th class="text-right">Rent (KES)</th>
                                <th class="text-right">Garbage (KES)</th>
                                <th class="text-right">Water (KES)</th>
                                <th class="text-right">Total due (KES)</th>
                                <th class="text-right">Paid (KES)</th>
                                <th class="text-right">Balance (KES)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($summary['months'] as $row)
                                <tr>
                                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $row['charge']->month_year)->format('F Y') }}</td>
                                    <td class="text-right">{{ number_format((float) $row['charge']->rent_amount, 2) }}</td>
                                    <td class="text-right">{{ number_format((float) $row['charge']->garbage_fee, 2) }}</td>
                                    <td class="text-right">{{ number_format((float) $row['charge']->water_bill, 2) }}</td>
                                    <td class="text-right">{{ number_format($row['due'], 2) }}</td>
                                    <td class="text-right">{{ number_format($row['paid'], 2) }}</td>
                                    <td class="text-right font-semibold">{{ number_format($row['balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-gray-500 p-5">No rent charges have been saved through this month.</td></tr>
                            @endforelse
                        </tbody>
                        @if(count($summary['months']))
                            <tfoot>
                                <tr class="font-bold bg-gray-50">
                                    <td>Totals</td>
                                    <td class="text-right">{{ number_format($summary['rent_total'], 2) }}</td>
                                    <td class="text-right">{{ number_format($summary['garbage_total'], 2) }}</td>
                                    <td class="text-right">{{ number_format($summary['water_total'], 2) }}</td>
                                    <td class="text-right">{{ number_format($summary['total_due'], 2) }}</td>
                                    <td class="text-right">{{ number_format($summary['total_paid'], 2) }}</td>
                                    <td class="text-right">{{ number_format($summary['arrears'], 2) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            <div>
                <h3 class="font-bold mb-3">Payments</h3>
                <div class="overflow-x-auto">
                    <table class="data-table w-full min-w-[750px]">
                        <thead>
                            <tr>
                                <th>Transaction date</th>
                                <th>Transaction code</th>
                                <th>Method</th>
                                <th>Message</th>
                                <th>Source</th>
                                <th class="text-right">Amount (KES)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tenancy->rentPayments as $payment)
                                <tr>
                                    <td>{{ $payment->paid_at->format('d M Y, h:i A') }}</td>
                                    <td>{{ $payment->transaction_code ?: '—' }}</td>
                                    <td>{{ $payment->payment_method ?: '—' }}</td>
                                    <td>{{ $payment->message ?: '—' }}</td>
                                    <td>{{ $payment->source === 'tenant' ? 'Tenant reported' : 'Landlord entered' }}</td>
                                    <td class="text-right">{{ number_format((float) $payment->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-gray-500 p-5">No approved payments are recorded through this month.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @empty
        <section class="page-card text-center text-gray-500 p-8">
            No tenancy or payment history is available for this tenant.
        </section>
    @endforelse
</div>
@endsection
