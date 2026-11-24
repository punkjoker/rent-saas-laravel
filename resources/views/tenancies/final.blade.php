@extends('layouts.landlord')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('tenancies.vacated') }}" class="text-green-700 hover:underline text-sm">
                ← Back to Vacated Tenancies
            </a>
            <h1 class="text-2xl font-bold text-gray-800 mt-3">Final Tenancy Reconciliation</h1>
            <p class="text-gray-500 mt-1">
                {{ $tenancy->tenant->full_name }} · {{ $tenancy->property->property_name }}
                · Unit {{ $tenancy->unit->unit_number }}
            </p>
        </div>
        <a href="{{ route('tenancies.reconciliation', $tenancy->id) }}" class="btn">
            Edit Reconciliation
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="page-card flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <p class="text-sm text-gray-500">Reconciliation status</p>
            <p class="font-semibold mt-1">
                {{ $reconciliation?->finalized_at ? 'Finalized '.$reconciliation->finalized_at->format('d M Y, h:i A') : 'Not finalized' }}
            </p>
        </div>
        <p class="text-sm text-gray-500">Records through {{ $asOfDate->format('d M Y') }}</p>
    </div>

    <div class="page-card flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
        <div>
            <p class="text-sm text-gray-500">Deposit refund status</p>
            @if($reconciliation?->refunded_at)
                <p class="font-semibold text-green-700 mt-1">
                    Refunded KES {{ number_format((float) $reconciliation->refund_amount, 2) }}
                    on {{ $reconciliation->refunded_at->format('d M Y, h:i A') }}
                </p>
                @if($reconciliation->refund_reference)
                    <p class="text-xs text-gray-500 mt-1">Reference: {{ $reconciliation->refund_reference }}</p>
                @endif
                @if($reconciliation->refund_paid_by)
                    <p class="text-xs text-gray-500 mt-1">Recorded by: {{ $reconciliation->refundPaidBy->name }}</p>
                @endif
            @elseif($summary['settlement_balance'] > 0 && $reconciliation?->finalized_at)
                <p class="font-semibold text-amber-700 mt-1">
                    Pending refund · KES {{ number_format($summary['settlement_balance'], 2) }}
                </p>
            @elseif($summary['settlement_balance'] <= 0)
                <p class="font-semibold text-gray-600 mt-1">No refundable deposit balance.</p>
            @else
                <p class="font-semibold text-gray-600 mt-1">Finalize the reconciliation before paying a refund.</p>
            @endif
        </div>

        @if(!$reconciliation?->refunded_at &&
            $reconciliation?->finalized_at &&
            $summary['settlement_balance'] > 0)
            <form method="POST" action="{{ route('tenancies.reconciliation.refund', $tenancy->id) }}"
                  class="flex flex-col sm:flex-row gap-2"
                  onsubmit="return confirm('Mark KES {{ number_format($summary['settlement_balance'], 2) }} as refunded to this tenant?')">
                @csrf
                <input type="text" name="refund_reference" maxlength="100"
                       placeholder="Transaction reference (optional)"
                       class="form-control">
                <button type="submit" class="btn btn-primary whitespace-nowrap">Mark refund paid</button>
            </form>
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
        <div class="page-card">
            <p class="text-sm text-gray-500">Agreed Deposit</p>
            <p class="text-xl font-bold mt-2">KES {{ number_format((float) $tenancy->deposit_amount, 2) }}</p>
        </div>
        <div class="page-card">
            <p class="text-sm text-gray-500">Rent Arrears</p>
            <p class="text-xl font-bold mt-2">KES {{ number_format($summary['rent']['arrears'], 2) }}</p>
        </div>
        <div class="page-card">
            <p class="text-sm text-gray-500">Deposit Collected</p>
            <p class="text-xl font-bold mt-2">KES {{ number_format($summary['deposit_collected'], 2) }}</p>
        </div>
        <div class="page-card">
            <p class="text-sm text-gray-500">Damage Costs</p>
            <p class="text-xl font-bold mt-2">KES {{ number_format($summary['damage_total'], 2) }}</p>
        </div>
        <div class="page-card">
            <p class="text-sm text-gray-500">Settlement Balance</p>
            <p class="text-xl font-bold mt-2 {{ $summary['settlement_balance'] < 0 ? 'text-red-700' : 'text-green-700' }}">
                KES {{ number_format($summary['settlement_balance'], 2) }}
            </p>
            <p class="text-xs text-gray-500 mt-1">
                {{ $summary['settlement_balance'] < 0 ? 'Still owed by tenant' : 'Refundable to tenant' }}
            </p>
        </div>
    </div>

    <div class="page-card">
        <h2 class="text-lg font-bold border-b pb-3">Damage and Deduction Details</h2>
        <div class="overflow-x-auto mt-3">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($damages as $damage)
                        <tr>
                            <td>{{ $damage->description }}</td>
                            <td>KES {{ number_format((float) $damage->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center py-5 text-gray-500">No damages or deductions recorded.</td>
                        </tr>
                    @endforelse
                    @if($damages->isNotEmpty())
                        <tr>
                            <td class="font-bold">Total Damage Costs</td>
                            <td class="font-bold">KES {{ number_format($summary['damage_total'], 2) }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    @include('tenancies.reconciliation-records')
</div>
@endsection
