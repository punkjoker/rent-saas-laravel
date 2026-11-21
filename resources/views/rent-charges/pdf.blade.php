<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rent Charges - {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 10px;
        }

        h1 {
            margin-bottom: 4px;
            color: #176b55;
        }

        p {
            margin-top: 0;
            color: #6b7280;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 7px;
            text-align: left;
        }

        th {
            background: #edf5f0;
        }

        .number {
            text-align: right;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <h1>Rent Charges Report</h1>
    <p>{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }} · Amounts in KES</p>
    @if($property)
        <p>
            <strong>Property:</strong> {{ $property->property_name }}
            @if($property->property_code)
                · <strong>Code:</strong> {{ $property->property_code }}
            @endif
            · <strong>Type:</strong> {{ ucfirst($property->property_type) }}
        </p>
        <p>
            <strong>Address:</strong> {{ $property->address ?: 'Not provided' }}
            @if($property->town || $property->county)
                · {{ collect([$property->town, $property->county])->filter()->implode(', ') }}
            @endif
        </p>
    @else
        <p><strong>Property:</strong> All Properties</p>
    @endif

    <table>
        <thead>
            <tr>
                <th>Property</th>
                <th>House Number</th>
                <th>Tenant</th>
                <th>Phone</th>
                <th class="number">Rent</th>
                <th class="number">Garbage</th>
                <th class="number">Water</th>
                <th class="number">Total Due</th>
            </tr>
        </thead>
        <tbody>
            @forelse($charges as $charge)
                <tr>
                    <td>{{ $charge->tenancy->property->property_name ?? 'N/A' }}</td>
                    <td>{{ $charge->tenancy->unit->unit_number ?? 'N/A' }}</td>
                    <td>{{ $charge->tenancy->tenant->full_name ?? 'Unknown Tenant' }}</td>
                    <td>{{ $charge->tenancy->tenant->phone ?? '—' }}</td>
                    <td class="number">{{ number_format((float) $charge->rent_amount, 2) }}</td>
                    <td class="number">{{ number_format((float) $charge->garbage_fee, 2) }}</td>
                    <td class="number">{{ number_format((float) $charge->water_bill, 2) }}</td>
                    <td class="number">{{ number_format((float) $charge->total_due, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">No saved rent charges match the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
            <tfoot>
                <tr>
                    <th colspan="4">REPORT TOTAL</th>
                    <th class="number">{{ number_format($totals['rent'], 2) }}</th>
                    <th class="number">{{ number_format($totals['garbage'], 2) }}</th>
                    <th class="number">{{ number_format($totals['water'], 2) }}</th>
                    <th class="number">{{ number_format($totals['total'], 2) }}</th>
                </tr>
            </tfoot>
    </table>
</body>
</html>
