@extends('layouts.landlord')

@section('title', 'Monthly Bills')
@section('page-heading', 'Monthly Bills')
@section('page-description', 'Filter and review saved monthly rent, garbage, and water charges.')

@section('content')
<div class="space-y-6">
    <section class="page-card">
        <form method="GET" action="{{ route('monthly-bills.index') }}"
              class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <label class="block">
                <span class="text-sm font-semibold">Billing month</span>
                <input type="month" name="month" value="{{ $month }}" required class="form-control mt-1">
            </label>

            <label class="block">
                <span class="text-sm font-semibold">Search house or tenant</span>
                <input type="search" name="search" value="{{ $search }}"
                       placeholder="House number or tenant name" class="form-control mt-1">
            </label>

            <label class="block">
                <span class="text-sm font-semibold">Property</span>
                <select name="property_id" class="form-control mt-1">
                    <option value="">All properties</option>
                    @foreach($properties as $property)
                        <option value="{{ $property->id }}" @selected((string) $propertyId === (string) $property->id)>
                            {{ $property->property_name }}
                        </option>
                    @endforeach
                </select>
            </label>

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">Filter report</button>
                <a href="{{ route('monthly-bills.index') }}" class="btn btn-secondary">Clear</a>
            </div>
        </form>
    </section>

    <section class="page-card">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-4">
            <div>
                <h2 class="text-xl font-bold">Saved monthly report</h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}
                    · {{ $savedCharges->count() }} charge records
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('rent-charges.export.excel', ['month' => $month, 'property_id' => $propertyId, 'search' => $search]) }}"
                   class="btn bg-green-700 text-white">Export Excel</a>
                <a href="{{ route('rent-charges.export.pdf', ['month' => $month, 'property_id' => $propertyId, 'search' => $search]) }}"
                   class="btn bg-red-700 text-white">Export PDF</a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table w-full min-w-[1050px]">
                <thead>
                    <tr>
                        <th>Property</th>
                        <th>House / unit</th>
                        <th>Tenant</th>
                        <th>Phone</th>
                        <th>Rent (KES)</th>
                        <th>Garbage (KES)</th>
                        <th>Water (KES)</th>
                        <th>Total due (KES)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($savedCharges as $charge)
                        @php($tenancy = $charge->tenancy)
                        <tr>
                            <td>{{ $tenancy->property->property_name ?? 'N/A' }}</td>
                            <td>{{ $tenancy->unit->unit_number ?? 'N/A' }}</td>
                            <td>{{ $tenancy->tenant->full_name ?? 'Unknown Tenant' }}</td>
                            <td>{{ $tenancy->tenant->phone ?? '—' }}</td>
                            <td>KES {{ number_format((float) $charge->rent_amount, 2) }}</td>
                            <td>KES {{ number_format((float) $charge->garbage_fee, 2) }}</td>
                            <td>KES {{ number_format((float) $charge->water_bill, 2) }}</td>
                            <td class="font-bold">KES {{ number_format((float) $charge->total_due, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-gray-500 p-6">
                                No saved rent charges match these filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($savedCharges->isNotEmpty())
                    <tfoot>
                        <tr class="font-bold bg-gray-50">
                            <td colspan="4" class="text-right p-4">REPORT TOTAL</td>
                            <td class="p-4">KES {{ number_format($chargeTotals['rent'], 2) }}</td>
                            <td class="p-4">KES {{ number_format($chargeTotals['garbage'], 2) }}</td>
                            <td class="p-4">KES {{ number_format($chargeTotals['water'], 2) }}</td>
                            <td class="p-4">KES {{ number_format($chargeTotals['total'], 2) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </section>
</div>
@endsection
