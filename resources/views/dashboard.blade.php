
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pango | Landlord Dashboard</title>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #f5f8f6;
            color: #17251f;
        }

        .sidebar {
            width: 250px;
            background: #142c24;
            color: white;
            position: fixed;
            inset: 0 auto 0 0;
            padding: 30px 20px;
        }

        .logo {
            font-size: 28px;
            font-weight: 800;
            color: white;
            letter-spacing: -1px;
            margin-bottom: 45px;
        }

        .logo span {
            color: #d5ae69;
        }

        .nav-label {
            font-size: 10px;
            color: #91a69a;
            letter-spacing: 1.5px;
            margin: 25px 12px 12px;
        }

        .nav-link {
            display: block;
            padding: 13px 14px;
            border-radius: 8px;
            color: #c8d7ce;
            text-decoration: none;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .nav-link.active {
            background: #24674f;
            color: white;
        }

        .nav-link:hover {
            background: #244c3b;
        }

        .main {
            margin-left: 250px;
            padding: 35px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 35px;
        }

        .topbar h1 {
            font-size: 25px;
        }

        .topbar p {
            color: #7b8981;
            font-size: 13px;
            margin-top: 5px;
        }

        .profile {
            background: white;
            border: 1px solid #e8eee9;
            border-radius: 10px;
            padding: 10px 16px;
            font-size: 13px;
            font-weight: 700;
        }

        .welcome {
            background: linear-gradient(110deg, #176b55, #248267);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 25px;
        }

        .welcome h2 {
            font-size: 24px;
            margin-bottom: 8px;
        }

        .welcome p {
            color: #d6ebe0;
            font-size: 14px;
        }

        .trial {
            background: white;
            border-radius: 12px;
            border: 1px solid #e6ede8;
            padding: 22px;
            margin-bottom: 25px;
        }

        .trial h3 {
            font-size: 16px;
            margin-bottom: 8px;
        }

        .trial p {
            font-size: 13px;
            color: #728078;
            margin-bottom: 15px;
        }

        .trial-bar {
            background: #e8eee9;
            height: 8px;
            border-radius: 20px;
            overflow: hidden;
        }

        .trial-progress {
            background: #238060;
            height: 100%;
            border-radius: 20px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            border: 1px solid #e9efeb;
            border-radius: 12px;
            padding: 23px;
        }

        .stat-card small {
            color: #78857e;
        }

        .stat-card h2 {
            font-size: 27px;
            margin-top: 10px;
        }

        .content-card {
            background: white;
            border: 1px solid #e9efeb;
            border-radius: 12px;
            padding: 25px;
        }

        .content-card h3 {
            margin-bottom: 10px;
        }

        .content-card p {
            color: #7b8981;
            font-size: 14px;
        }

        .logout {
            background: #b94b4b;
            color: white;
            border: 0;
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            margin-top: 30px;
            cursor: pointer;
            font-weight: 700;
        }

        @media(max-width: 850px) {
            .sidebar {
                width: 190px;
            }

            .main {
                margin-left: 190px;
                padding: 20px;
            }

            .stats {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media(max-width: 600px) {
            .sidebar {
                position: static;
                width: 100%;
                padding: 15px;
            }

            .logo {
                margin-bottom: 15px;
            }

            .sidebar nav {
                display: flex;
                overflow-x: auto;
                gap: 5px;
            }

            .nav-label {
                display: none;
            }

            .nav-link {
                white-space: nowrap;
            }

            .main {
                margin-left: 0;
                padding: 18px;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

<aside class="sidebar">

    <div class="logo">PANGO<span>.</span></div>

    <nav>
        <div class="nav-label">MAIN MENU</div>

        <a href="{{ route('dashboard') }}" class="nav-link active">
            ◫ Dashboard
        </a>

        <a href="{{ route('properties.index') }}" class="nav-link">
    ⌂ Properties
</a>
        <a href="{{ route('tenants.index') }}" class="nav-link">
    ♙ Tenants
</a>
<a href="{{ route('tenancies.index') }}"
   class="nav-link {{ request()->routeIs('tenancies.*') ? 'active' : '' }}">
    <i class="fas fa-file-contract"></i>
    <span>♙Tenancies</span>
</a>
<a href="{{ route('rent-charges.index') }}"
   class="nav-link {{ request()->routeIs('rent-charges.*') ? 'active' : '' }}">
    <i class="fas fa-file-invoice-dollar"></i>
    <span>Rent Charges</span>
</a>
        <a href="#" class="nav-link">▣ Rent Payments</a>
        <a href="#" class="nav-link">◈ Deposits</a>
        <a href="#" class="nav-link">⚡ Monthly Bills</a>

        <div class="nav-label">MANAGEMENT</div>

        <a href="#" class="nav-link">▤ Reports</a>
        <a href="#" class="nav-link">⚙ Settings</a>
    </nav>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="logout">Sign Out</button>
    </form>

</aside>


<main class="main">

    <div class="topbar">

        <div>
            <h1>Dashboard</h1>
            <p>Welcome to your Pango property management workspace.</p>
        </div>

        <div class="profile">
            {{ $user->name }}
        </div>

    </div>


    @if(session('success'))
        <div style="background:#e4f5e9;color:#176b55;padding:14px;border-radius:9px;margin-bottom:20px;">
            {{ session('success') }}
        </div>
    @endif


    <section class="welcome">

        <h2>Hello, {{ $user->name }} 👋</h2>

        <p>
            Welcome to {{ $landlord->business_name ?? 'your Pango account' }}.
            Your rental management workspace is ready.
        </p>

    </section>


    @if($subscription && $subscription->status === 'trial')

        @php
            $trialEnd = $subscription->trial_ends_at;
            $daysLeft = $trialEnd && $trialEnd->isFuture()
                ? (int) ceil(now()->diffInSeconds($trialEnd) / 86400)
                : 0;

            $progress = min(100, max(0, (($daysLeft / 14) * 100)));
        @endphp

        <section class="trial">

            <h3>
                Your Free Trial
                <span style="color:#176b55;font-size:12px;">
                    {{ $daysLeft }} days remaining
                </span>
            </h3>

            <p>
                Your 14-day trial
                @if($trialEnd)
                    ends on {{ $trialEnd->format('d M Y, h:i A') }}.
                @endif
                Explore Pango and set up your rental business.
            </p>

            <div class="trial-bar">
                <div class="trial-progress"
                     style="width:{{ $progress }}%;">
                </div>
            </div>

        </section>

    @elseif($subscription && $subscription->status === 'active')

        <section class="trial">
            <h3 style="color:#176b55;">Active Subscription</h3>
            <p>Your Pango subscription is active.</p>
        </section>

    @else

        <section class="trial">
            <h3>Subscription Required</h3>
            <p>Your account does not currently have an active subscription.</p>
        </section>

    @endif


    <section class="stats">

        <div class="stat-card">
            <small>Properties</small>
            <h2>{{ $landlord?->properties()->count() ?? 0 }}</h2>
        </div>

        <div class="stat-card">
            <small>Current Subscription</small>
            <h2 style="font-size:19px;">
                {{ $subscription?->status ?? 'None' }}
            </h2>
        </div>

        <div class="stat-card">
            <small>Account Created</small>
            <h2 style="font-size:19px;">
                {{ $user->created_at->format('d M Y') }}
            </h2>
        </div>

    </section>


    <section class="content-card">

        <h3>Get started with Pango</h3>

        <p>
            Your account has been created successfully.
            The next step is to register your first property,
            add rental units and start managing your tenants.
        </p>

    </section>

</main>

</body>
</html>
