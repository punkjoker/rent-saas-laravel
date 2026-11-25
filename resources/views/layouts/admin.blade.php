<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') | Pango Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/pango-glass.css') }}">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f3f7f4; color: #17251f; font-family: 'DM Sans', sans-serif; }
        .admin-layout { min-height: 100vh; display: grid; grid-template-columns: 230px minmax(0, 1fr); }
        .admin-sidebar { display: flex; flex-direction: column; padding: 24px 16px; background: #142c24; color: white; }
        .admin-logo { margin: 0 10px 24px; color: white; font-size: 23px; font-weight: 800; text-decoration: none; }
        .admin-logo span { color: #d5ae69; }
        .admin-sidebar nav { display: grid; gap: 5px; }
        .admin-nav-link { padding: 11px 12px; border-radius: 9px; color: #c8d7ce; text-decoration: none; font-size: 13px; }
        .admin-nav-link.active, .admin-nav-link:hover { background: #24674f; color: white; }
        .admin-main { min-width: 0; padding: 30px clamp(16px, 3vw, 40px); }
        .admin-topbar { display: flex; justify-content: space-between; align-items: flex-start; gap: 18px; margin-bottom: 24px; }
        .admin-topbar h1 { font-size: 25px; }
        .admin-topbar p { margin-top: 5px; color: #728078; font-size: 13px; }
        .admin-card { min-width: 0; margin-bottom: 16px; padding: 20px; border: 1px solid #e4ece6; border-radius: 15px; background: rgba(255,255,255,.9); box-shadow: 0 8px 24px rgba(20,44,36,.04); }
        .admin-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 13px; }
        .admin-stat span { display: block; color: #718078; font-size: 11px; font-weight: 700; }
        .admin-stat strong { display: block; margin-top: 9px; color: #20392e; font-size: 24px; }
        .admin-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .admin-table-wrap { overflow-x: auto; }
        .admin-table { width: 100%; border-collapse: collapse; min-width: 700px; font-size: 12px; }
        .admin-table th, .admin-table td { padding: 11px 10px; border-bottom: 1px solid #edf1ee; text-align: left; vertical-align: middle; }
        .admin-table th { color: #718078; font-size: 10px; text-transform: uppercase; letter-spacing: .05em; }
        .admin-form { display: grid; gap: 8px; min-width: 180px; }
        .admin-input { width: 100%; padding: 9px 10px; border: 1px solid #dfe7e1; border-radius: 8px; font: inherit; font-size: 12px; }
        .admin-button { display: inline-flex; align-items: center; justify-content: center; padding: 8px 11px; border: 0; border-radius: 8px; color: white; background: #176b55; font: inherit; font-size: 11px; font-weight: 700; text-decoration: none; cursor: pointer; }
        .admin-button-danger { background: #b94b4b; }
        .admin-button-neutral { color: #30483b; background: #e8eee9; }
        .admin-actions { display: flex; flex-wrap: wrap; gap: 6px; }
        .admin-muted { color: #718078; font-size: 11px; }
        .admin-badge { display: inline-flex; padding: 5px 8px; border-radius: 999px; background: #eef2ef; font-size: 10px; font-weight: 800; text-transform: capitalize; }
        .admin-badge-approved { color: #176b55; background: #e5f4eb; }
        .admin-badge-pending { color: #94620d; background: #fff3d8; }
        .admin-badge-rejected, .admin-badge-revoked { color: #a83232; background: #fff0f0; }
        .admin-error { padding: 12px; border-radius: 9px; color: #a83232; background: #fff0f0; margin-bottom: 12px; }
        .income-chart { display: grid; grid-template-columns: repeat(12, minmax(24px, 1fr)); gap: 7px; height: 190px; overflow-x: auto; }
        .income-column { display: grid; min-width: 24px; grid-template-rows: 1fr auto; gap: 7px; text-align: center; }
        .income-column-track { display: flex; align-items: flex-end; justify-content: center; border-bottom: 1px solid #e0e8e2; }
        .income-column-bar { width: 60%; min-height: 2px; border-radius: 5px 5px 0 0; background: linear-gradient(180deg,#73c7a0,#238060); }
        .income-label { color: #718078; font-size: 9px; white-space: nowrap; }
        @media(max-width: 950px) { .admin-layout { grid-template-columns: 1fr; } .admin-sidebar { padding: 13px; } .admin-logo { margin-bottom: 10px; } .admin-sidebar nav { display: flex; overflow-x: auto; } .admin-nav-link { white-space: nowrap; } .admin-stats { grid-template-columns: repeat(2,minmax(0,1fr)); } }
        @media(max-width: 650px) { .admin-main { padding: 18px 12px; } .admin-grid { grid-template-columns: 1fr; } .admin-topbar { flex-direction: column; } }
    </style>
    @stack('styles')
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <a href="{{ route('admin.dashboard') }}" class="admin-logo">PANGO<span>.</span> ADMIN</a>
        <nav>
            <a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Overview</a>
            <a href="{{ route('admin.accounts') }}" class="admin-nav-link {{ request()->routeIs('admin.accounts') ? 'active' : '' }}">Landlord accounts</a>
            <a href="{{ route('admin.subscriptions') }}" class="admin-nav-link {{ request()->routeIs('admin.subscriptions') ? 'active' : '' }}">Subscriptions</a>
            <a href="{{ route('admin.properties') }}" class="admin-nav-link {{ request()->routeIs('admin.properties') ? 'active' : '' }}">Properties</a>
            <a href="{{ route('admin.listings') }}" class="admin-nav-link {{ request()->routeIs('admin.listings') ? 'active' : '' }}">Property listings</a>
            <a href="{{ route('admin.vacancies') }}" class="admin-nav-link {{ request()->routeIs('admin.vacancies*') ? 'active' : '' }}">Vacancies</a>
        </nav>
    </aside>
    <main class="admin-main">
        <header class="admin-topbar">
            <div>
                <h1>@yield('heading', 'Admin overview')</h1>
                <p>@yield('description', 'Manage Pango accounts and marketplace activity.')</p>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="admin-button admin-button-neutral" type="submit">Sign out</button>
            </form>
        </header>
        @if(session('success'))<div class="admin-card" style="color:#176b55">{{ session('success') }}</div>@endif
        @if($errors->any())
            <div class="admin-error">
                @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif
        @yield('content')
    </main>
</div>
@stack('scripts')
</body>
</html>
