@extends('layouts.landlord')

@section('content')

<div class="page-card">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Rent Charges</h1>
            <p class="text-gray-500 mt-1">
                Monthly rent, garbage and water charge register.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-5 p-4 rounded-lg bg-green-100 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 p-4 rounded-lg bg-red-100 text-red-800">
            <p class="font-semibold mb-2">Please correct the following:</p>
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Month and property filters --}}
    <div class="bg-gray-50 border rounded-xl p-4 mb-6">
        <form method="GET"
              action="{{ route('rent-charges.index') }}"
              class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">

            <div>
                <label class="block text-sm font-semibold mb-2">
                    Billing Month
                </label>

                <input
                    type="month"
                    name="month"
                    value="{{ $month }}"
                    required
                    class="form-control w-full"
                >
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">
                    Search House or Tenant
                </label>

                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="House number or tenant name"
                    class="form-control w-full"
                >
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">
                    Property
                </label>

                <select name="property_id" class="form-control w-full">
                    <option value="">All Properties</option>

                    @foreach($properties as $property)
                        <option
                            value="{{ $property->id }}"
                            {{ (string) $propertyId === (string) $property->id ? 'selected' : '' }}
                        >
                            {{ $property->property_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <button type="submit" class="btn w-full">
                    Filter Register
                </button>
            </div>

        </form>
    </div>

    {{-- Register information --}}
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-2 mb-4">
        <div>
            <h2 class="font-bold text-lg">
                {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}
            </h2>

            <p class="text-sm text-gray-500">
                {{ $tenancies->count() }} tenancy record(s) found.
            </p>
        </div>

        <div class="text-sm text-gray-500">
            Amounts are in KES
        </div>
    </div>

    @if($tenancies->isNotEmpty())

        @php
            $oldCharges = collect(old('charges', []))->keyBy('tenancy_id');
        @endphp

        <form method="POST" action="{{ route('rent-charges.store') }}" id="chargesForm">

            @csrf

            <input type="hidden" name="month_year" value="{{ $month }}">

            <input type="hidden" name="property_id" value="{{ $propertyId }}">

            <input type="hidden" name="search" value="{{ $search }}">

            <div class="overflow-x-auto">
                <table class="data-table w-full min-w-[1050px]">

                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>House / Unit</th>
                            <th>Tenant</th>
                            <th>Phone</th>
                            <th>Rent (KES)</th>
                            <th>Garbage (KES)</th>
                            <th>Water (KES)</th>
                            <th>Total (KES)</th>
                            <th>Record</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($tenancies as $index => $tenancy)

                            @php
                                $savedCharge = $tenancy->rentCharges->first();

                                $oldRow = $oldCharges->get($tenancy->id, []);

                                $rent = $oldRow['rent_amount']
                                    ?? $savedCharge?->rent_amount
                                    ?? $tenancy->monthly_rent;

                                $garbage = $oldRow['garbage_fee']
                                    ?? $savedCharge?->garbage_fee
                                    ?? $tenancy->garbage_fee;

                                $water = $oldRow['water_bill']
                                    ?? $savedCharge?->water_bill
                                    ?? 0;

                                $total = (float) $rent + (float) $garbage + (float) $water;

                                $tenantName = trim(
                                    ($tenancy->tenant->first_name ?? '') . ' ' .
                                    ($tenancy->tenant->middle_name ?? '') . ' ' .
                                    ($tenancy->tenant->last_name ?? '')
                                );

                                $effectiveEndDate = $tenancy->ended_at
                                    ?? $tenancy->lease_end_date;
                            @endphp

                            <tr class="charge-row">

                                <td>
                                    <div class="font-semibold">
                                        {{ $tenancy->property->property_name ?? 'N/A' }}
                                    </div>
                                </td>

                                <td>
                                    <div class="font-bold">
                                        {{ $tenancy->unit->unit_number ?? 'N/A' }}
                                    </div>

                                    @if($tenancy->status === 'ended')
                                        <span class="text-xs text-red-600">
                                            Ended
                                        </span>
                                    @elseif($tenancy->status === 'upcoming')
                                        <span class="text-xs text-blue-600">
                                            Upcoming
                                        </span>
                                    @else
                                        <span class="text-xs text-green-600">
                                            Active
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="font-medium">
                                        {{ $tenantName ?: 'Unknown Tenant' }}
                                    </div>

                                    <div class="text-xs text-gray-400 mt-1">
                                        From {{ $tenancy->start_date?->format('d M Y') }}

                                        @if($effectiveEndDate)
                                            to {{ \Carbon\Carbon::parse($effectiveEndDate)->format('d M Y') }}
                                        @else
                                            onwards
                                        @endif
                                    </div>
                                </td>

                                <td>{{ $tenancy->tenant->phone ?? '—' }}</td>

                                <td>
                                    <input
                                        type="hidden"
                                        name="charges[{{ $index }}][tenancy_id]"
                                        value="{{ $tenancy->id }}"
                                    >

                                    <input
                                        type="number"
                                        name="charges[{{ $index }}][rent_amount]"
                                        value="{{ $rent }}"
                                        min="0"
                                        step="0.01"
                                        required
                                        class="form-control charge-input rent-input w-32"
                                    >
                                </td>

                                <td>
                                    <input
                                        type="number"
                                        name="charges[{{ $index }}][garbage_fee]"
                                        value="{{ $garbage }}"
                                        min="0"
                                        step="0.01"
                                        required
                                        class="form-control charge-input garbage-input w-32"
                                    >
                                </td>

                                <td>
                                    <input
                                        type="number"
                                        name="charges[{{ $index }}][water_bill]"
                                        value="{{ $water }}"
                                        min="0"
                                        step="0.01"
                                        required
                                        class="form-control charge-input water-input w-32"
                                    >
                                </td>

                                <td>
                                    <span class="row-total font-bold whitespace-nowrap">
                                        KES {{ number_format($total, 2) }}
                                    </span>
                                </td>

                                <td>
                                    @if($savedCharge)
                                        <span class="text-green-700 text-xs font-semibold">
                                            Saved
                                        </span>
                                    @else
                                        <span class="text-gray-500 text-xs">
                                            New
                                        </span>
                                    @endif
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                    <tfoot>
                        <tr class="font-bold bg-gray-50">
                            <td colspan="4" class="text-right p-4">
                                REGISTER TOTAL
                            </td>

                            <td class="p-4"><span id="rentTotal">KES 0.00</span></td>
                            <td class="p-4"><span id="garbageTotal">KES 0.00</span></td>
                            <td class="p-4"><span id="waterTotal">KES 0.00</span></td>
                            <td class="p-4"><span id="grandTotal">KES 0.00</span></td>
                            <td></td>
                        </tr>
                    </tfoot>

                </table>
            </div>

            <div class="mt-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                <p class="text-sm text-gray-500">
                    Changes to existing charges will update the selected month's records.
                </p>

                <button
                    type="submit"
                    class="btn px-8 py-3 font-bold"
                    id="saveChargesButton"
                >
                    Save Charges
                </button>

            </div>

        </form>

    @else

        <div class="border border-dashed rounded-xl p-10 text-center">
            <h3 class="font-bold text-lg mb-2">
                No Tenancies Found
            </h3>

            <p class="text-gray-500">
                No tenancies overlap
                {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}.

                Try another month or property.
            </p>
        </div>

    @endif

    <section class="mt-10 border-t pt-7">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-4">
            <div>
                <h2 class="text-xl font-bold">Saved Rent Charges</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Saved records for {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}.
                    Tenant names and phone numbers are shown separately.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('rent-charges.export.excel', ['month' => $month, 'property_id' => $propertyId, 'search' => $search]) }}"
                   class="btn bg-green-700 text-white">
                    Export Excel
                </a>
                <a href="{{ route('rent-charges.export.pdf', ['month' => $month, 'property_id' => $propertyId, 'search' => $search]) }}"
                   class="btn bg-red-700 text-white">
                    Export PDF
                </a>
            </div>
        </div>

        @if($savedCharges->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="data-table w-full min-w-[1050px]">
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>House / Unit</th>
                            <th>Tenant</th>
                            <th>Phone</th>
                            <th>Rent (KES)</th>
                            <th>Garbage (KES)</th>
                            <th>Water (KES)</th>
                            <th>Total Due (KES)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($savedCharges as $charge)
                            @php $tenancy = $charge->tenancy; @endphp
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
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="font-bold bg-gray-50">
                            <td colspan="4" class="text-right p-4">SAVED CHARGES TOTAL</td>
                            <td class="p-4">KES {{ number_format($chargeTotals['rent'], 2) }}</td>
                            <td class="p-4">KES {{ number_format($chargeTotals['garbage'], 2) }}</td>
                            <td class="p-4">KES {{ number_format($chargeTotals['water'], 2) }}</td>
                            <td class="p-4">KES {{ number_format($chargeTotals['total'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="border border-dashed rounded-xl p-8 text-center text-gray-500">
                No saved rent charges match this month and search.
            </div>
        @endif
    </section>

</div>

@endsection


@push('styles')
<style>
    .charge-input {
        min-width: 105px;
        padding: 9px 10px;
        border: 1px solid #d1d5db;
        border-radius: 7px;
        background: #fff;
    }

    .charge-input:focus {
        outline: none;
        border-color: #15803d;
        box-shadow: 0 0 0 2px rgba(21, 128, 61, 0.12);
    }

    .data-table th,
    .data-table td {
        padding: 13px 12px;
        vertical-align: middle;
    }

    .data-table thead th {
        white-space: nowrap;
    }

    .data-table tbody tr:hover {
        background: #f9fafb;
    }
</style>
@endpush


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const rows = document.querySelectorAll('.charge-row');
    const grandTotalElement = document.getElementById('grandTotal');
    const rentTotalElement = document.getElementById('rentTotal');
    const garbageTotalElement = document.getElementById('garbageTotal');
    const waterTotalElement = document.getElementById('waterTotal');

    function formatKES(amount) {
        return new Intl.NumberFormat('en-KE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
    }

    function calculateTotals() {

        let rentTotal = 0;
        let garbageTotal = 0;
        let waterTotal = 0;
        let grandTotal = 0;

        rows.forEach(function (row) {

            const rent = parseFloat(
                row.querySelector('.rent-input').value
            ) || 0;

            const garbage = parseFloat(
                row.querySelector('.garbage-input').value
            ) || 0;

            const water = parseFloat(
                row.querySelector('.water-input').value
            ) || 0;

            const rowTotal = rent + garbage + water;

            row.querySelector('.row-total').textContent =
                'KES ' + formatKES(rowTotal);

            rentTotal += rent;
            garbageTotal += garbage;
            waterTotal += water;
            grandTotal += rowTotal;
        });

        if (rentTotalElement) {
            rentTotalElement.textContent =
                'KES ' + formatKES(rentTotal);
        }

        if (garbageTotalElement) {
            garbageTotalElement.textContent =
                'KES ' + formatKES(garbageTotal);
        }

        if (waterTotalElement) {
            waterTotalElement.textContent =
                'KES ' + formatKES(waterTotal);
        }

        if (grandTotalElement) {
            grandTotalElement.textContent =
                'KES ' + formatKES(grandTotal);
        }
    }

    document.querySelectorAll('.charge-input').forEach(function (input) {
        input.addEventListener('input', calculateTotals);
    });

    calculateTotals();

});
</script>
@endpush