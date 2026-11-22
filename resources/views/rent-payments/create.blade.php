@extends('layouts.landlord')

@section('title', 'Record Rent Payment')
@section('page-heading', 'Record Rent Payment')

@section('content')
<div class="page-card max-w-3xl">
    <p class="text-gray-600 mb-5">
        {{ $tenancy->tenant->full_name }} · {{ $tenancy->property->property_name }}
        · House {{ $tenancy->unit->unit_number }}
    </p>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg mb-5">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('payments.store', $tenancy->id) }}" class="space-y-4">
        @csrf
        <label class="block">
            <span class="text-sm font-semibold">Amount (KES)</span>
            <input type="number" name="amount" min="0.01" step="0.01" required value="{{ old('amount') }}" class="form-control mt-1">
        </label>
        <label class="block">
            <span class="text-sm font-semibold">Transaction date and time</span>
            <input type="datetime-local" name="paid_at" required value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}" class="form-control mt-1">
        </label>
        <label class="block">
            <span class="text-sm font-semibold">Transaction code</span>
            <input type="text" name="transaction_code" maxlength="100" value="{{ old('transaction_code') }}" class="form-control mt-1">
        </label>
        <label class="block">
            <span class="text-sm font-semibold">Payment method</span>
            <input type="text" name="payment_method" maxlength="50" value="{{ old('payment_method') }}" placeholder="Cash, bank, M-Pesa..." class="form-control mt-1">
        </label>
        <label class="block">
            <span class="text-sm font-semibold">Payment message / note</span>
            <textarea name="message" maxlength="2000" rows="3" class="form-control mt-1">{{ old('message') }}</textarea>
        </label>
        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">Save payment</button>
            <a href="{{ route('payments.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
