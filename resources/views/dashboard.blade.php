
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
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        .sidebar nav {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            overscroll-behavior: contain;
        }

        .sidebar form {
            flex: 0 0 auto;
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

        .dashboard-charts {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 25px;
        }

        .chart-card {
            background: white;
            border: 1px solid #e6ede8;
            border-radius: 14px;
            padding: 22px;
            min-width: 0;
            box-shadow: 0 8px 24px rgba(20, 44, 36, .04);
        }

        .chart-card-wide {
            grid-column: 1 / -1;
        }

        .chart-heading {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 18px;
        }

        .chart-heading h3 {
            font-size: 16px;
            margin-bottom: 5px;
        }

        .chart-heading p {
            color: #78857e;
            font-size: 12px;
        }

        .chart-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 16px;
            color: #65756c;
            font-size: 12px;
        }

        .chart-legend span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .legend-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            display: inline-block;
        }

        .bar-chart {
            height: 220px;
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 12px;
            padding: 10px 4px 0;
        }

        .bar-chart-group {
            min-width: 0;
            height: 100%;
            display: grid;
            grid-template-rows: 1fr auto;
            gap: 8px;
            text-align: center;
        }

        .bar-chart-pair {
            height: 100%;
            min-height: 0;
            display: flex;
            justify-content: center;
            align-items: flex-end;
            gap: 5px;
            border-bottom: 1px solid #e9efeb;
        }

        .chart-bar {
            width: min(30px, 42%);
            min-height: 0;
            border-radius: 5px 5px 0 0;
            transition: height .25s ease;
        }

        .chart-bar-billed {
            background: linear-gradient(180deg, #72c4a0, #238060);
        }

        .chart-bar-collected {
            background: linear-gradient(180deg, #8cb4f4, #477dd1);
        }

        .chart-bar-expense {
            background: linear-gradient(180deg, #c0a9ef, #8461c6);
        }

        .bar-chart-label {
            color: #78857e;
            font-size: 11px;
            white-space: nowrap;
        }

        .occupancy-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 28px;
            min-height: 220px;
        }

        .occupancy-chart {
            width: 170px;
            height: 170px;
            flex: 0 0 auto;
        }

        .occupancy-legend {
            display: grid;
            gap: 12px;
            color: #65756c;
            font-size: 13px;
        }

        .occupancy-legend-item {
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .occupancy-legend-item strong {
            color: #17251f;
            margin-left: auto;
        }

        .chart-empty {
            color: #78857e;
            font-size: 12px;
            margin-top: 12px;
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

        @media(max-width: 1000px) {
            .dashboard-charts {
                grid-template-columns: 1fr;
            }

            .chart-card-wide {
                grid-column: auto;
            }
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
                display: block;
                height: auto;
                overflow: visible;
            }

            .logo {
                margin-bottom: 15px;
            }

            .sidebar nav {
                display: flex;
                overflow-x: auto;
                overflow-y: hidden;
                gap: 5px;
                flex: 0 0 auto;
            }

            .nav-label {
                display: none;
            }

            .nav-link {
                white-space: nowrap;
                flex: 0 0 auto;
            }

            .main {
                margin-left: 0;
                padding: 18px;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .chart-card {
                padding: 17px;
            }

            .bar-chart {
                gap: 6px;
                height: 190px;
            }

            .occupancy-content {
                flex-direction: column;
                gap: 18px;
            }

            .occupancy-legend {
                width: 100%;
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/pango-glass.css') }}">
</head>

<body>

<aside class="sidebar">

    <div class="logo">PANGO<span>.</span></div>

    <nav>
        <div class="nav-label">MAIN MENU</div>

        <a href="{{ route('dashboard') }}"
           class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('properties.index') }}"
           class="nav-link {{ request()->routeIs('properties.*') || request()->routeIs('units.*') ? 'active' : '' }}">
            <i class="fas fa-building"></i>
            <span>Properties</span>
        </a>
        <a href="{{ route('property-listings.index') }}"
           class="nav-link {{ request()->routeIs('property-listings.*') ? 'active' : '' }}">
            <i class="fas fa-bullhorn"></i>
            <span>Advertise Property</span>
        </a>
        <a href="{{ route('landlord-vacancies.index') }}"
           class="nav-link {{ request()->routeIs('landlord-vacancies.*') ? 'active' : '' }}">
            <i class="fas fa-door-open"></i>
            <span>Vacancies</span>
        </a>
        <a href="{{ route('tenants.index') }}"
           class="nav-link {{ request()->routeIs('tenants.*') ? 'active' : '' }}">
            <i class="fas fa-users"></i>
            <span>Tenants</span>
        </a>
        <a href="{{ route('tenancies.index') }}"
           class="nav-link {{ request()->routeIs('tenancies.index') || request()->routeIs('tenancies.create') || request()->routeIs('tenancies.show') ? 'active' : '' }}">
            <i class="fas fa-file-contract"></i>
            <span>Tenancies</span>
        </a>
        <a href="{{ route('tenancies.vacated') }}"
           class="nav-link {{ request()->routeIs('tenancies.vacated') || request()->routeIs('tenancies.reconciliation*') ? 'active' : '' }}">
            <i class="fas fa-box-archive"></i>
            <span>Vacated Tenancies</span>
        </a>
        <a href="{{ route('rent-charges.index') }}"
           class="nav-link {{ request()->routeIs('rent-charges.*') ? 'active' : '' }}">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>Rent Charges</span>
        </a>
        <a href="{{ route('monthly-bills.index') }}"
           class="nav-link {{ request()->routeIs('monthly-bills.*') ? 'active' : '' }}">
            <i class="fas fa-calendar-alt"></i>
            <span>Monthly Bills</span>
        </a>
        <a href="{{ route('expenses.index') }}"
           class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
            <i class="fas fa-receipt"></i>
            <span>Expenses</span>
        </a>
        <a href="{{ route('payments.index') }}"
           class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">
            <i class="fas fa-money-bill-wave"></i>
            <span>Rent Payments</span>
        </a>
        <a href="{{ route('deposits.index') }}"
           class="nav-link {{ request()->routeIs('deposits.*') ? 'active' : '' }}">
            <i class="fas fa-piggy-bank"></i>
            <span>Deposits</span>
        </a>
        <div class="nav-label">MANAGEMENT</div>
        <a href="{{ route('reports.index') }}"
           class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i class="fas fa-chart-pie"></i>
            <span>Reports</span>
        </a>
        <a href="#" class="nav-link">
            <i class="fas fa-gear"></i>
            <span>Settings</span>
        </a>
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

    @php
        $monthlyRentMax = max(1, collect($dashboardCharts['monthly'])->max(
            fn ($month) => max($month['billed'], $month['collected'])
        ) ?? 0);
        $monthlyExpenseMax = max(1, collect($dashboardCharts['monthly'])->max('expenses') ?? 0);
        $occupancy = $dashboardCharts['occupancy'];
        $donutCircumference = 314.16;
        $donutSegments = [
            ['label' => 'Occupied', 'count' => $occupancy['occupied'], 'color' => '#238060'],
            ['label' => 'Vacant', 'count' => $occupancy['vacant'], 'color' => '#477dd1'],
            ['label' => 'Maintenance', 'count' => $occupancy['maintenance'], 'color' => '#e5a83b'],
            ['label' => 'Other', 'count' => $occupancy['other'], 'color' => '#9aa7a0'],
        ];
        $donutOffset = 0;
    @endphp

    <section class="dashboard-charts" aria-label="Property performance charts">
        <article class="chart-card chart-card-wide">
            <div class="chart-heading">
                <div>
                    <h3>Rent billed vs payments received</h3>
                    <p>Monthly charges compared with approved payments received over the last six months.</p>
                </div>
            </div>

            <div class="bar-chart" role="img" aria-label="Monthly rent billed and payment collection bar chart">
                @foreach($dashboardCharts['monthly'] as $month)
                    @php
                        $billedHeight = $month['billed'] > 0
                            ? max(($month['billed'] / $monthlyRentMax) * 100, 3)
                            : 0;
                        $collectedHeight = $month['collected'] > 0
                            ? max(($month['collected'] / $monthlyRentMax) * 100, 3)
                            : 0;
                    @endphp
                    <div class="bar-chart-group">
                        <div class="bar-chart-pair">
                            <span class="chart-bar chart-bar-billed"
                                  style="height:{{ $billedHeight }}%"
                                  title="{{ $month['label'] }} billed: KES {{ number_format($month['billed'], 2) }}"></span>
                            <span class="chart-bar chart-bar-collected"
                                  style="height:{{ $collectedHeight }}%"
                                  title="{{ $month['label'] }} received: KES {{ number_format($month['collected'], 2) }}"></span>
                        </div>
                        <span class="bar-chart-label">{{ $month['label'] }}</span>
                    </div>
                @endforeach
            </div>
            <div class="chart-legend">
                <span><i class="legend-dot" style="background:#238060"></i>Rent billed</span>
                <span><i class="legend-dot" style="background:#477dd1"></i>Payments received</span>
            </div>
        </article>

        <article class="chart-card">
            <div class="chart-heading">
                <div>
                    <h3>Monthly expenses</h3>
                    <p>Recorded expenses for the last six months.</p>
                </div>
            </div>

            <div class="bar-chart" role="img" aria-label="Monthly expenses bar chart">
                @foreach($dashboardCharts['monthly'] as $month)
                    @php
                        $expenseHeight = $month['expenses'] > 0
                            ? max(($month['expenses'] / $monthlyExpenseMax) * 100, 3)
                            : 0;
                    @endphp
                    <div class="bar-chart-group">
                        <div class="bar-chart-pair">
                            <span class="chart-bar chart-bar-expense"
                                  style="height:{{ $expenseHeight }}%"
                                  title="{{ $month['label'] }} expenses: KES {{ number_format($month['expenses'], 2) }}"></span>
                        </div>
                        <span class="bar-chart-label">{{ $month['label'] }}</span>
                    </div>
                @endforeach
            </div>
            <div class="chart-legend">
                <span><i class="legend-dot" style="background:#8461c6"></i>Expenses recorded</span>
            </div>
        </article>

        <article class="chart-card">
            <div class="chart-heading">
                <div>
                    <h3>Unit occupancy</h3>
                    <p>Current unit status across your properties.</p>
                </div>
            </div>

            <div class="occupancy-content">
                <svg class="occupancy-chart"
                     viewBox="0 0 160 160"
                     role="img"
                     aria-label="Occupancy: {{ $occupancy['occupied'] }} occupied, {{ $occupancy['vacant'] }} vacant, {{ $occupancy['maintenance'] }} under maintenance, {{ $occupancy['other'] }} other">
                    <circle cx="80" cy="80" r="50" fill="none" stroke="#edf1ee" stroke-width="18"></circle>
                    @foreach($donutSegments as $segment)
                        @php
                            $segmentLength = $occupancy['total'] > 0
                                ? $donutCircumference * ($segment['count'] / $occupancy['total'])
                                : 0;
                            $segmentStart = $donutOffset;
                            $donutOffset += $segmentLength;
                        @endphp
                        @if($segment['count'] > 0)
                            <circle cx="80" cy="80" r="50" fill="none"
                                    stroke="{{ $segment['color'] }}" stroke-width="18"
                                    stroke-dasharray="{{ $segmentLength }} {{ $donutCircumference - $segmentLength }}"
                                    stroke-dashoffset="{{ -$segmentStart }}"
                                    transform="rotate(-90 80 80)"></circle>
                        @endif
                    @endforeach
                    <text x="80" y="77" text-anchor="middle" fill="#17251f" font-size="25" font-weight="700">
                        {{ $occupancy['total'] }}
                    </text>
                    <text x="80" y="98" text-anchor="middle" fill="#78857e" font-size="11">total units</text>
                </svg>

                <div class="occupancy-legend">
                    @foreach($donutSegments as $segment)
                        <div class="occupancy-legend-item">
                            <i class="legend-dot" style="background:{{ $segment['color'] }}"></i>
                            <span>{{ $segment['label'] }}</span>
                            <strong>{{ $segment['count'] }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </article>
    </section>


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
