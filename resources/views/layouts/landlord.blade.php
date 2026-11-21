
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Pango') | Pango</title>

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
            overflow-y: auto;
            z-index: 100;
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
            transition: .2s;
        }

        .nav-link.active {
            background: #24674f;
            color: white;
        }

        .nav-link:hover {
            background: #244c3b;
            color: white;
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

        .main {
            margin-left: 250px;
            padding: 35px;
            min-height: 100vh;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
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

        .alert {
            padding: 14px 18px;
            border-radius: 9px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #e4f5e9;
            color: #176b55;
        }

        .alert-error {
            background: #fff0f0;
            color: #a83232;
        }

        .page-card {
            background: white;
            border: 1px solid #e9efeb;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 22px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 11px 17px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
        }

        .btn-primary {
            background: #176b55;
            color: white;
        }

        .btn-primary:hover {
            background: #125641;
        }

        .btn-secondary {
            background: #edf2ee;
            color: #30463b;
        }

        .btn-danger {
            background: #fff0f0;
            color: #a83232;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 7px;
        }

        .form-control {
            width: 100%;
            padding: 12px 13px;
            border: 1px solid #dce5df;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            outline: none;
            background: white;
        }

        .form-control:focus {
            border-color: #238060;
            box-shadow: 0 0 0 3px rgba(35,128,96,.1);
        }

        .form-help {
            display: block;
            color: #849087;
            font-size: 11px;
            margin-top: 5px;
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 650px;
        }

        .data-table th {
            text-align: left;
            padding: 14px 12px;
            color: #78857e;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .5px;
            background: #f8faf8;
        }

        .data-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #edf1ee;
            font-size: 13px;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .status-active, .status-occupied {
            background: #e4f5e9;
            color: #176b55;
        }

        .status-inactive, .status-vacant {
            background: #fff2df;
            color: #98611b;
        }

        .status-maintenance {
            background: #e9edff;
            color: #4d5eae;
        }

        .pagination {
            margin-top: 20px;
        }

        .pagination nav {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        @media(max-width: 850px) {
            .sidebar {
                width: 190px;
            }

            .main {
                margin-left: 190px;
                padding: 22px;
            }
        }

        @media(max-width: 600px) {
            .sidebar {
                position: relative;
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

            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>

    @stack('styles')
</head>

<body>

<aside class="sidebar">

    <div class="logo">PANGO<span>.</span></div>

    <nav>
        <div class="nav-label">MAIN MENU</div>

        <a href="{{ route('dashboard') }}"
           class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            ◫ Dashboard
        </a>

        <a href="{{ route('properties.index') }}"
           class="nav-link {{ request()->routeIs('properties.*') || request()->routeIs('units.*') ? 'active' : '' }}">
            ⌂ Properties
        </a>

        <a href="{{ route('tenants.index') }}"
   class="nav-link {{ request()->routeIs('tenants.*') ? 'active' : '' }}">
    ♙ Tenants
</a>
<a href="{{ route('tenancies.index') }}"
   class="nav-link {{ request()->routeIs('tenancies.*') ? 'active' : '' }}">
    <i class="fas fa-file-contract"></i>
    <span>Tenancies</span>
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
            <h1>@yield('page-heading', 'Dashboard')</h1>
            <p>@yield('page-description', 'Welcome to your Pango property management workspace.')</p>
        </div>

        <div class="profile">
            {{ auth()->user()->name }}
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            <strong>Please correct the following:</strong>
            <ul style="margin:8px 0 0 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')

</main>

@stack('scripts')

</body>
</html>
