@extends('layouts.landlord')

@section('content')
@php
    $damageRows = old('damages', $damages->map(function ($damage) {
        return ['description' => $damage->description, 'amount' => $damage->amount];
    })->all());
@endphp

<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('tenancies.vacated') }}" class="text-green-700 hover:underline text-sm">
                ← Back to Vacated Tenancies
            </a>
            <h1 class="text-2xl font-bold text-gray-800 mt-3">Tenancy Reconciliation</h1>
            <p class="text-gray-500 mt-1">
                {{ $tenancy->tenant->full_name }} · {{ $tenancy->property->property_name }}
                · Unit {{ $tenancy->unit->unit_number }}
            </p>
        </div>
        <a href="{{ route('tenancies.reconciliation.final', $tenancy->id) }}"
           class="btn bg-gray-200 text-gray-700">
            Final View
        </a>
    </div>

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
        <h2 class="text-lg font-bold">Damage and Deduction Entries</h2>
        <p class="text-sm text-gray-500 mt-1">
            Financial records are included through {{ $asOfDate->format('d M Y') }}.
        </p>

        <form method="POST" action="{{ route('tenancies.reconciliation.save', $tenancy->id) }}" class="mt-4">
            @csrf
            @method('PUT')
            <div class="overflow-x-auto">
                <table class="data-table w-full">
                    <thead>
                        <tr>
                            <th>Damage / Deduction</th>
                            <th>Amount (KES)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="damage-rows">
                        @foreach($damageRows as $index => $damage)
                            <tr>
                                <td>
                                    <input type="text"
                                           name="damages[{{ $index }}][description]"
                                           value="{{ $damage['description'] ?? '' }}"
                                           maxlength="255"
                                           required
                                           class="form-control"
                                           placeholder="e.g. Broken window">
                                </td>
                                <td>
                                    <input type="number"
                                           name="damages[{{ $index }}][amount]"
                                           value="{{ $damage['amount'] ?? '' }}"
                                           min="0.01"
                                           step="0.01"
                                           required
                                           class="form-control"
                                           placeholder="0.00">
                                </td>
                                <td>
                                    <button type="button" class="text-red-700 hover:underline" onclick="this.closest('tr').remove()">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap gap-2 mt-4">
                <button type="button" id="add-damage" class="btn bg-gray-200 text-gray-700">
                    + Add Damage
                </button>
                <button type="submit" class="btn">
                    Save Reconciliation
                </button>
            </div>
        </form>
    </div>

    @include('tenancies.reconciliation-records')

    <div class="flex justify-end">
        <form method="POST"
              action="{{ route('tenancies.reconciliation.finalize', $tenancy->id) }}"
              onsubmit="return confirm('Mark this reconciliation as final? You can still edit it later.');">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn bg-green-700 hover:bg-green-800 text-white">
                {{ $reconciliation?->finalized_at ? 'Update Final Status' : 'Mark as Final' }}
            </button>
        </form>
    </div>
</div>

<template id="damage-row-template">
    <tr>
        <td>
            <input type="text"
                   name="damages[__INDEX__][description]"
                   maxlength="255"
                   required
                   class="form-control"
                   placeholder="e.g. Broken window">
        </td>
        <td>
            <input type="number"
                   name="damages[__INDEX__][amount]"
                   min="0.01"
                   step="0.01"
                   required
                   class="form-control"
                   placeholder="0.00">
        </td>
        <td>
            <button type="button" class="text-red-700 hover:underline" onclick="this.closest('tr').remove()">
                Remove
            </button>
        </td>
    </tr>
</template>

<script>
    (function () {
        const tableBody = document.getElementById('damage-rows');
        const template = document.getElementById('damage-row-template');
        let nextIndex = tableBody.querySelectorAll('tr').length;

        document.getElementById('add-damage').addEventListener('click', function () {
            const row = template.content.cloneNode(true);
            row.querySelectorAll('[name]').forEach(function (input) {
                input.name = input.name.replace('__INDEX__', nextIndex);
            });
            nextIndex += 1;
            tableBody.appendChild(row);
        });
    })();
</script>
@endsection
