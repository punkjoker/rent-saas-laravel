<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rent payment receipt</title>
    <style>
        body{font-family:Arial,sans-serif;color:#24352b;margin:30px}
        main{max-width:700px;margin:0 auto;border:1px solid #dfe8e2;padding:28px;border-radius:10px}
        h1{margin-top:0}.row{display:flex;justify-content:space-between;gap:15px;border-bottom:1px solid #e8eee9;padding:12px 0}
        .breakdown{width:100%;border-collapse:collapse;margin:12px 0}
        .breakdown th,.breakdown td{text-align:left;padding:9px 7px;border-bottom:1px solid #e8eee9}
        .breakdown .number{text-align:right}
        .actions{display:flex;gap:10px;margin-top:22px}.actions a,.actions button{padding:10px 14px;border:0;border-radius:6px;background:#176b45;color:white;text-decoration:none;cursor:pointer}
        @media(max-width:600px){body{margin:12px}main{padding:16px}.breakdown th,.breakdown td{font-size:13px;padding:7px 4px}}
        @media print{body{margin:0}.actions{display:none}main{border:0}}
    </style>
</head>
<body>
<main>
    <h1>Rent payment receipt</h1>
    <p>Receipt #{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</p>
    <div class="row"><span>Tenant</span><strong>{{ $tenancy->tenant->full_name }}</strong></div>
    <div class="row"><span>Property / house</span><strong>{{ $tenancy->property->property_name }} / {{ $tenancy->unit->unit_number }}</strong></div>
    <div class="row"><span>Payment date</span><strong>{{ $payment->paid_at->format('d M Y, h:i A') }}</strong></div>
    <div class="row"><span>Transaction code</span><strong>{{ $payment->transaction_code ?: '—' }}</strong></div>
    <div class="row"><span>Payment method</span><strong>{{ $payment->payment_method ?: '—' }}</strong></div>
    <div class="row"><span>Amount received</span><strong>KES {{ number_format((float) $payment->amount, 2) }}</strong></div>
    <h2>Rent breakdown</h2>
    <p>Payment allocation is applied to the oldest outstanding rent charges first.</p>
    @if(count($paymentAllocations['allocations']))
        <div style="overflow-x:auto">
            <table class="breakdown">
                <thead>
                    <tr>
                        <th>Charge month</th>
                        <th class="number">Rent</th>
                        <th class="number">Garbage</th>
                        <th class="number">Water</th>
                        <th class="number">Total due</th>
                        <th class="number">Applied</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paymentAllocations['allocations'] as $allocation)
                        <tr>
                            <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $allocation['charge']->month_year)->format('M Y') }}</td>
                            <td class="number">{{ number_format((float) $allocation['charge']->rent_amount, 2) }}</td>
                            <td class="number">{{ number_format((float) $allocation['charge']->garbage_fee, 2) }}</td>
                            <td class="number">{{ number_format((float) $allocation['charge']->water_bill, 2) }}</td>
                            <td class="number">{{ number_format((float) $allocation['charge']->total_due, 2) }}</td>
                            <td class="number">{{ number_format($allocation['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p>No outstanding rent charge was available to apply this payment against.</p>
    @endif
    @if($paymentAllocations['unallocated'] > 0)
        <div class="row">
            <span>Unallocated amount held as credit</span>
            <strong>KES {{ number_format($paymentAllocations['unallocated'], 2) }}</strong>
        </div>
    @endif
    @if($payment->message)
        <div class="row"><span>Message</span><strong>{{ $payment->message }}</strong></div>
    @endif
    <div class="actions">
        <button type="button" onclick="window.print()">Print receipt</button>
        <a href="{{ route('tenant-payments.show', ['token' => $token]) }}">Payment history</a>
    </div>
</main>
</body>
</html>
