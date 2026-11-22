<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rent history and payments</title>
    <style>
        body{font-family:Arial,sans-serif;background:#f4f7f5;color:#24352b;margin:0;padding:24px}
        main{max-width:1000px;margin:auto}.card{background:#fff;border:1px solid #dfe8e2;border-radius:12px;padding:22px;margin-bottom:20px}
        h1,h2{margin-top:0}table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:10px;border-bottom:1px solid #e8eee9}
        .right{text-align:right}.muted{color:#64756a}.success{background:#e8f7ed;color:#17633b;padding:12px;border-radius:8px}
        .error{background:#fff0f0;color:#9e2525;padding:12px;border-radius:8px;margin:6px 0}
        input,textarea,button{font:inherit;padding:10px;border:1px solid #cbd8cf;border-radius:6px;width:100%;box-sizing:border-box}
        button{background:#176b45;color:white;border:0;font-weight:bold;cursor:pointer}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:14px}
        label{display:block;font-weight:bold;font-size:14px}label input,label textarea{display:block;margin-top:6px;font-weight:normal}
        @media(max-width:600px){body{padding:12px}.card{padding:16px}th,td{font-size:13px;padding:7px}}
    </style>
</head>
<body>
<main>
    <section class="card">
        <h1>Rent payment history</h1>
        <p class="muted">
            {{ $tenancy->tenant->full_name }} · {{ $tenancy->property->property_name }}
            · House {{ $tenancy->unit->unit_number }}
        </p>
        <div class="grid">
            <p>Total charges: <strong>KES {{ number_format($summary['total_due'], 2) }}</strong></p>
            <p>Paid toward charges: <strong>KES {{ number_format($summary['total_paid'], 2) }}</strong></p>
            <p>Arrears: <strong>KES {{ number_format($summary['arrears'], 2) }}</strong></p>
            @if($summary['credit'] > 0)
                <p>Credit: <strong>KES {{ number_format($summary['credit'], 2) }}</strong></p>
            @endif
        </div>
    </section>

    @if(session('success'))
        <p class="success">{{ session('success') }}</p>
    @endif
    @foreach($errors->all() as $error)<p class="error">{{ $error }}</p>@endforeach

    <section class="card">
        <h2>Monthly charges and balance</h2>
        <div style="overflow-x:auto">
            <table>
                <thead><tr><th>Month</th><th class="right">Due (KES)</th><th class="right">Paid (KES)</th><th class="right">Balance (KES)</th></tr></thead>
                <tbody>
                    @forelse($summary['months'] as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $row['charge']->month_year)->format('F Y') }}</td>
                            <td class="right">{{ number_format($row['due'], 2) }}</td>
                            <td class="right">{{ number_format($row['paid'], 2) }}</td>
                            <td class="right">{{ number_format($row['balance'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No monthly charges have been saved yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="card">
        <h2>Approved payments and receipts</h2>
        <div style="overflow-x:auto">
            <table>
                <thead><tr><th>Date</th><th>Transaction code</th><th class="right">Amount (KES)</th><th>Receipt</th></tr></thead>
                <tbody>
                    @forelse($tenancy->rentPayments as $payment)
                        <tr>
                            <td>{{ $payment->paid_at->format('d M Y, h:i A') }}</td>
                            <td>{{ $payment->transaction_code ?: '—' }}</td>
                            <td class="right">{{ number_format((float) $payment->amount, 2) }}</td>
                            <td><a href="{{ route('tenant-payments.receipt', ['token' => $token, 'payment' => $payment->id]) }}">View receipt</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No approved payments recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="card">
        <h2>Payment reports</h2>
        <p class="muted">Reports remain pending until the landlord verifies the transaction. An approved payment appears in your ledger and has a receipt.</p>
        <ul>
            @forelse($tenancy->rentPaymentSubmissions as $submission)
                <li style="margin:12px 0">
                    {{ $submission->transaction_date->format('d M Y, h:i A') }}
                    · KES {{ number_format((float) $submission->amount, 2) }}
                    · Code {{ $submission->transaction_code }}
                    · <strong>{{ ucfirst($submission->status) }}</strong>
                    @if($submission->review_message) — {{ $submission->review_message }} @endif
                </li>
            @empty
                <li>No payment reports submitted.</li>
            @endforelse
        </ul>
    </section>

    <section class="card">
        <h2>Report a payment</h2>
        <form method="POST" action="{{ route('tenant-payments.submit', ['token' => $token]) }}" class="grid">
            @csrf
            <label>Amount (KES)<input type="number" name="amount" min="0.01" step="0.01" required value="{{ old('amount') }}"></label>
            <label>Transaction date and time<input type="datetime-local" name="transaction_date" required value="{{ old('transaction_date', now()->format('Y-m-d\TH:i')) }}"></label>
            <label>Transaction code<input type="text" name="transaction_code" maxlength="100" required value="{{ old('transaction_code') }}"></label>
            <label style="grid-column:1/-1">Message (optional)<textarea name="message" maxlength="2000" rows="3">{{ old('message') }}</textarea></label>
            <button type="submit">Submit for approval</button>
        </form>
    </section>
</main>
</body>
</html>
