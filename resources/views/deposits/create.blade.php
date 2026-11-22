@extends('layouts.landlord')

@section('title', 'Record Deposit Payment')
@section('page-heading', 'Record Deposit Payment')

@section('content')
<div class="page-card max-w-3xl">
    <div class="mb-5">
        <h2 class="font-bold text-lg">{{ $tenancy->tenant->full_name }}</h2>
        <p class="text-gray-600">{{ $tenancy->property->property_name }} · House {{ $tenancy->unit->unit_number }}</p>
        <p class="text-sm text-gray-500 mt-2">
            Agreed deposit: KES {{ number_format((float) $tenancy->deposit_amount, 2) }}
            · Remaining balance: KES {{ number_format($balance, 2) }}
        </p>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg mb-5">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('deposits.store', $tenancy->id) }}" class="space-y-4">
        @csrf
        <label class="block">
            <span class="text-sm font-semibold">Amount received (KES)</span>
            <input type="number" name="amount" min="0.01" step="0.01" required
                   value="{{ old('amount', $balance > 0 ? number_format($balance, 2, '.', '') : '') }}"
                   class="form-control mt-1">
        </label>
        <label class="block">
            <span class="text-sm font-semibold">Payment date and time</span>
            <input type="datetime-local" name="paid_at" required
                   value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}"
                   class="form-control mt-1">
        </label>
        <label class="block">
            <span class="text-sm font-semibold">Transaction code</span>
            <input type="text" name="transaction_code" maxlength="100"
                   value="{{ old('transaction_code') }}" class="form-control mt-1">
        </label>
        <label class="block">
            <span class="text-sm font-semibold">Payment method</span>
            <input type="text" name="payment_method" maxlength="50"
                   value="{{ old('payment_method') }}" placeholder="Cash, bank, M-Pesa..."
                   class="form-control mt-1">
        </label>
        <label class="block">
            <span class="text-sm font-semibold">Message / note</span>
            <textarea name="message" maxlength="2000" rows="3" class="form-control mt-1">{{ old('message') }}</textarea>
        </label>
        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">Save deposit payment</button>
            <a href="{{ route('deposits.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
