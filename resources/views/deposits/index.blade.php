@extends('layouts.landlord')

@section('title', 'Deposits')
@section('page-heading', 'Deposits')
@section('page-description', 'Record deposit payments and monitor collected amounts and outstanding balances.')

@push('styles')
<style>
    .deposit-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .deposit-summary-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        min-width: 0;
        padding: 18px;
        background: #fff;
        border: 1px solid #e5ebe7;
        border-radius: 12px;
    }

    .deposit-summary-item strong {
        text-align: right;
        white-space: nowrap;
    }

    .deposit-table th,
    .deposit-table td {
        padding: 8px 10px;
        vertical-align: middle;
    }

    .deposit-actions {
        display: flex;
        flex-flow: row nowrap;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
    }

    .deposit-actions .btn {
        padding: 6px 9px;
        font-size: 12px;
        line-height: 1.2;
    }

    @media (max-width: 760px) {
        .deposit-summary {
            grid-template-columns: 1fr;
        }
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
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <div class="deposit-summary">
        <div class="deposit-summary-item">
            <span class="text-sm text-gray-500">Agreed deposits</span>
            <strong class="text-lg">KES {{ number_format($totals['agreed'], 2) }}</strong>
        </div>
        <div class="deposit-summary-item">
            <span class="text-sm text-gray-500">Deposits collected</span>
            <strong class="text-lg">KES {{ number_format($totals['collected'], 2) }}</strong>
        </div>
        <div class="deposit-summary-item">
            <span class="text-sm text-gray-500">Deposit balances outstanding</span>
            <strong class="text-lg">KES {{ number_format($totals['balance'], 2) }}</strong>
        </div>
    </div>

    <section class="page-card">
        <form method="GET" action="{{ route('deposits.index') }}" class="flex flex-col sm:flex-row sm:items-end gap-3 mb-5">
            <label class="block flex-1 min-w-0">
                <span class="text-sm font-semibold">Search house number or tenant name</span>
                <input type="search" name="search" value="{{ $search }}"
                       placeholder="Enter a house number or name"
                       class="form-control mt-1">
            </label>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">Search</button>
                @if($search !== '')
                    <a href="{{ route('deposits.index') }}" class="btn btn-secondary">Clear</a>
                @endif
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="data-table deposit-table w-full min-w-[950px]">
                <thead>
                    <tr>
                        <th>Property</th>
                        <th>House</th>
                        <th>Tenant</th>
                        <th class="text-right">Agreed (KES)</th>
                        <th class="text-right">Collected (KES)</th>
                        <th class="text-right">Balance (KES)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenancies as $row)
                        @php($tenancy = $row['tenancy'])
                        <tr>
                            <td>{{ $tenancy->property->property_name }}</td>
                            <td>{{ $tenancy->unit->unit_number }}</td>
                            <td>{{ $tenancy->tenant->full_name }}</td>
                            <td class="text-right">{{ number_format((float) $tenancy->deposit_amount, 2) }}</td>
                            <td class="text-right">{{ number_format($row['collected'], 2) }}</td>
                            <td class="text-right font-bold">{{ number_format($row['balance'], 2) }}</td>
                            <td>
                                <div class="deposit-actions">
                                    <a href="{{ route('deposits.create', $tenancy->id) }}"
                                       class="btn btn-primary" title="Record deposit payment">Record</a>
                                    <a href="{{ route('deposits.history', $tenancy->id) }}"
                                       class="btn btn-secondary" title="View deposit payment history">History</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-gray-500 p-6">
                                @if($search !== '')
                                    No deposits match this house number or tenant name.
                                @else
                                    No tenancies are available to track deposits.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
