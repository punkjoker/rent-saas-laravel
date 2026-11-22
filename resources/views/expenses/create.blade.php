@extends('layouts.landlord')

@section('title', 'Record Expense')
@section('page-heading', 'Record Expense')
@section('page-description', 'Add a general business expense to your monthly records.')

@section('content')
<div class="page-card max-w-3xl">
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg mb-5">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('expenses.store') }}" class="space-y-4">
        @csrf
        <label class="block">
            <span class="text-sm font-semibold">Expense date</span>
            <input type="date" name="expense_date" required max="{{ now()->toDateString() }}"
                   value="{{ old('expense_date', now()->toDateString()) }}"
                   class="form-control mt-1">
        </label>
        <label class="block">
            <span class="text-sm font-semibold">Category</span>
            <input type="text" name="category" required maxlength="100"
                   value="{{ old('category') }}" placeholder="Repairs, transport, utilities..."
                   class="form-control mt-1">
        </label>
        <label class="block">
            <span class="text-sm font-semibold">Amount (KES)</span>
            <input type="number" name="amount" required min="0.01" step="0.01"
                   value="{{ old('amount') }}" class="form-control mt-1">
        </label>
        <label class="block">
            <span class="text-sm font-semibold">Description</span>
            <textarea name="description" required maxlength="2000" rows="4"
                      class="form-control mt-1">{{ old('description') }}</textarea>
        </label>
        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">Save expense</button>
            <a href="{{ route('expenses.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
