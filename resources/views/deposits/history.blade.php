@extends('layouts.landlord')

@section('title', 'Deposit History')
@section('page-heading', 'Deposit History')
@section('page-description', 'Deposit payments recorded for this tenancy.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold">{{ $tenancy->tenant->full_name }}</h2>
            <p class="text-gray-500">{{ $tenancy->property->property_name }} · House {{ $tenancy->unit->unit_number }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('deposits.index') }}" class="btn btn-secondary">Back to deposits</a>
            <a href="{{ route('deposits.create', $tenancy->id) }}" class="btn btn-primary">Record payment</a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="page-card">
            <p class="text-sm text-gray-500">Agreed deposit</p>
            <p class="text-xl font-bold mt-1">KES {{ number_format((float) $tenancy->deposit_amount, 2) }}</p>
        </div>
        <div class="page-card">
            <p class="text-sm text-gray-500">Collected</p>
            <p class="text-xl font-bold mt-1">KES {{ number_format($collected, 2) }}</p>
        </div>
        <div class="page-card">
            <p class="text-sm text-gray-500">Outstanding balance</p>
            <p class="text-xl font-bold mt-1">KES {{ number_format($balance, 2) }}</p>
        </div>
    </div>

    <section class="page-card">
        <div class="overflow-x-auto">
            <table class="data-table w-full min-w-[800px]">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Transaction code</th>
                        <th>Method</th>
                        <th>Message</th>
                        <th class="text-right">Amount (KES)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->paid_at->format('d M Y, h:i A') }}</td>
                            <td>{{ $payment->transaction_code ?: '—' }}</td>
                            <td>{{ $payment->payment_method ?: '—' }}</td>
                            <td>{{ $payment->message ?: '—' }}</td>
                            <td class="text-right">{{ number_format((float) $payment->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-gray-500 p-6">No deposit payments have been recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
