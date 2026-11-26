
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pango | Subscription Plans</title>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #f5f8f6;
            color: #182820;
            padding: 40px 5%;
        }

        .container { max-width: 1150px; margin: auto; }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            gap: 15px;
        }

        .logo {
            color: #176b55;
            font-size: 28px;
            font-weight: 800;
        }

        .back {
            color: #176b55;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
        }

        .intro { text-align: center; margin-bottom: 35px; }

        .intro h1 { font-size: 35px; margin-bottom: 10px; }

        .intro p { color: #718078; }

        .notice {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .error { background: #fff0f0; color: #b33b3b; }
        .success { background: #e4f5e9; color: #176b55; }

        .current {
            background: white;
            padding: 22px;
            border-radius: 12px;
            border: 1px solid #e6eee8;
            margin-bottom: 30px;
        }

        .current h3 { margin-bottom: 8px; }
        .current p { color: #748178; font-size: 14px; }

        .plans {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
        }

        .plan {
            background: white;
            padding: 25px 20px;
            border-radius: 14px;
            border: 1px solid #e3ebe5;
            display: flex;
            flex-direction: column;
        }

        .plan.featured {
            border: 2px solid #176b55;
            box-shadow: 0 12px 30px rgba(23,107,85,.09);
        }

        .plan h2 { font-size: 20px; }

        .price {
            font-size: 30px;
            font-weight: 800;
            margin: 15px 0;
        }

        .price small {
            font-size: 12px;
            font-weight: 400;
            color: #7c8981;
        }

        .limit {
            background: #eaf5ed;
            color: #176b55;
            padding: 8px;
            text-align: center;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .plan ul {
            list-style: none;
            flex: 1;
            margin-bottom: 25px;
        }

        .plan li {
            padding: 8px 0;
            font-size: 13px;
            color: #66746c;
            border-bottom: 1px solid #f0f3f0;
        }

        .choose {
            display: block;
            text-align: center;
            padding: 12px;
            background: #176b55;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 13px;
        }

        .choose:hover { background: #105340; }
            .checkout-form { display: grid; gap: 8px; margin-top: 12px; }
            .checkout-form input { width: 100%; padding: 10px; border: 1px solid #dfe7e1; border-radius: 8px; }

        .history {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-top: 35px;
            overflow-x: auto;
        }

        table { width: 100%; border-collapse: collapse; }
        th, td {
            text-align: left;
            padding: 13px;
            border-bottom: 1px solid #edf1ed;
            font-size: 13px;
        }

        th { color: #718078; }

        @media(max-width: 1000px) {
            .plans { grid-template-columns: repeat(2, 1fr); }
        }

        @media(max-width: 550px) {
            body { padding: 22px 4%; }
            .plans { grid-template-columns: 1fr; }
            .intro h1 { font-size: 27px; }
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/pango-glass.css') }}">
</head>

<body>

<div class="container">

    <div class="header">
        <div class="logo">PANGO.</div>
        <a href="{{ route('dashboard') }}" class="back">
            ← Back to Dashboard
        </a>
    </div>

    <div class="intro">
        <h1>Choose Your Subscription</h1>
        <p>Manage your properties without limits holding your business back.</p>
    </div>

    @if(session('error'))
        <div class="notice error">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="notice success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="notice error">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="current">
        <h3>Current Subscription</h3>

        @if($subscription)
            <p>
                Status:
                <strong>{{ ucfirst($subscription->status) }}</strong>
                &nbsp; | &nbsp;

                Expiry:
                <strong>
                    {{ $subscription->ends_at
                        ? $subscription->ends_at->format('d M Y')
                        : 'Not set' }}
                </strong>
            </p>
        @else
            <p>You do not currently have a subscription.</p>
        @endif
    </div>

    @if($access && $access->expires_at->isFuture())
        <div class="notice success">
            Accommodation access is active until <strong>{{ $access->expires_at->format('d M Y') }}</strong>.
        </div>
    @endif

    <div class="plans">

        @foreach($plans as $plan)

            <div class="plan {{ $plan->slug === 'biashara' ? 'featured' : '' }}">

                <h2>{{ $plan->name }}</h2>

                <div class="price">
                    {{ $plan->currency }} {{ number_format($plan->price) }}
                    <small>/ month</small>
                </div>

                <div class="limit">
                    {{ $plan->tenant_limit === null
                        ? 'Unlimited tenants'
                        : 'Up to '.$plan->tenant_limit.' tenants' }}
                </div>

                <ul>
                    @foreach($plan->features ?? [] as $feature)
                        <li>✓ {{ $feature }}</li>
                    @endforeach
                </ul>

                <form method="POST" action="{{ route('billing.subscribe') }}" class="checkout-form">
                    @csrf
                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                    <input type="tel" name="phone" required
                           placeholder="M-Pesa number (e.g. 0712345678)"
                           value="{{ old('phone', $landlord->phone) }}">
                    <button class="choose" type="submit">Pay & choose {{ $plan->name }}</button>
                </form>

            </div>

        @endforeach

    </div>

        <div class="current" id="accommodation-access" style="margin-top:30px">
            <h2 style="margin-bottom:8px">Unlock Accommodations for your landlord account</h2>
            <p>Publish stays and manage booking requests. Monthly renewals are initiated from your account; M-Pesa will prompt you to approve each payment.</p>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:18px;margin-top:18px">
                <form method="POST" action="{{ route('billing.accommodations') }}" class="checkout-form">
                    @csrf
                    <input type="hidden" name="period" value="monthly">
                    <strong>KES 3,500 / month</strong>
                    <input type="tel" name="phone" required placeholder="M-Pesa number"
                           value="{{ old('phone', $landlord->phone) }}">
                    <button class="choose" type="submit">Pay monthly</button>
                </form>
                <form method="POST" action="{{ route('billing.accommodations') }}" class="checkout-form">
                    @csrf
                    <input type="hidden" name="period" value="yearly">
                    <strong>KES 33,600 / year <small>(20% off the monthly rate)</small></strong>
                    <input type="tel" name="phone" required placeholder="M-Pesa number"
                           value="{{ old('phone', $landlord->phone) }}">
                    <button class="choose" type="submit">Pay yearly</button>
                </form>
            </div>
            @unless(config('services.mpesa.consumer_key') && config('services.mpesa.consumer_secret') &&
                config('services.mpesa.shortcode') && config('services.mpesa.passkey') &&
                config('services.mpesa.callback_url') && config('services.mpesa.callback_token'))
                <p style="margin-top:14px;color:#94620d;font-size:12px">
                    M-Pesa checkout is not yet configured. Ask the administrator to configure Daraja credentials and a public HTTPS callback URL.
                </p>
            @endunless
        </div>

    <div class="history">

        <h2 style="font-size:19px;margin-bottom:20px;">
            Subscription Payment History
        </h2>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>M-Pesa Receipt</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->created_at->format('d M Y') }}</td>
                        <td>
                            {{ $payment->currency }}
                            {{ number_format($payment->amount, 2) }}
                        </td>
                        <td>{{ strtoupper($payment->provider) }}</td>
                        <td>{{ $payment->mpesa_receipt_number ?? 'Pending' }}</td>
                        <td>
                            {{ ucfirst($payment->status) }}
                            @if(($payment->metadata['purchase'] ?? null) === 'accommodation_addon')
                                <small> · Accommodations {{ $payment->metadata['period'] ?? '' }}</small>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No subscription payments recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

</div>

</body>
</html>
