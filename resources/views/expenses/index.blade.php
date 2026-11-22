@extends('layouts.landlord')

@section('title', 'Expenses')
@section('page-heading', 'Expenses')
@section('page-description', 'Record and review general business expenses by month.')

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

    <section class="page-card">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <form method="GET" action="{{ route('expenses.index') }}" class="flex flex-col sm:flex-row sm:items-end gap-3 flex-1">
                <label class="block sm:flex-1 min-w-0">
                    <span class="text-sm font-semibold">Month</span>
                    <input type="month" name="month" value="{{ $month }}" class="form-control mt-1">
                </label>
                <label class="block sm:flex-[1.5] min-w-0">
                    <span class="text-sm font-semibold">Search category or description</span>
                    <input type="search" name="search" value="{{ $search }}"
                           placeholder="Enter a category or keyword"
                           class="form-control mt-1">
                </label>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    @if($search !== '')
                        <a href="{{ route('expenses.index', ['month' => $month]) }}" class="btn btn-secondary">Clear</a>
                    @endif
                </div>
            </form>
            <a href="{{ route('expenses.create') }}" class="btn btn-primary">+ Record expense</a>
        </div>
    </section>

    <section class="page-card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
            <h2 class="font-bold text-lg">
                Expenses for {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}
            </h2>
            <p class="font-semibold">
                Matching total: KES {{ number_format($total, 2) }}
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table w-full min-w-[800px]">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th class="text-right">Amount (KES)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_date->format('d M Y') }}</td>
                            <td>{{ $expense->category }}</td>
                            <td>{{ $expense->description }}</td>
                            <td class="text-right font-semibold">{{ number_format((float) $expense->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-gray-500 p-6">
                                @if($search !== '')
                                    No expenses match this month and search.
                                @else
                                    No expenses have been recorded for this month.
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
