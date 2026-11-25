<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Available rentals') | Pango</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/pango-glass.css') }}">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; color: #17251f; background: #f5f8f6; font-family: 'DM Sans', sans-serif; }
        .public-header { padding: 18px 5%; background: rgba(255,255,255,.92); border-bottom: 1px solid #e6ede8; }
        .public-header-inner { max-width: 1180px; margin: auto; display: flex; justify-content: space-between; align-items: center; gap: 20px; }
        .public-logo { color: #176b55; font-size: 24px; font-weight: 800; text-decoration: none; }
        .public-logo span { color: #d5ae69; }
        .public-home-link { color: #176b55; font-size: 13px; font-weight: 700; text-decoration: none; }
        .public-container { width: min(1120px, calc(100% - 36px)); margin: 36px auto 70px; }
        .public-back { color: #176b55; font-weight: 700; font-size: 13px; text-decoration: none; }
        .public-detail-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(300px, .9fr); gap: 34px; margin-top: 20px; }
        .public-image-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; align-content: start; }
        .public-listing-image { width: 100%; aspect-ratio: 4/3; object-fit: cover; border-radius: 17px; }
        .public-listing-image:first-child { grid-column: 1 / -1; aspect-ratio: 16/10; }
        .public-listing-no-image { grid-column: 1 / -1; display: grid; min-height: 260px; place-items: center; border-radius: 17px; background: #e8eee9; color: #65756c; }
        .public-eyebrow { color: #176b55; text-transform: uppercase; letter-spacing: 1.4px; font-size: 11px; font-weight: 800; }
        .public-title { margin: 9px 0; font-size: clamp(26px, 4vw, 38px); line-height: 1.15; letter-spacing: -.04em; }
        .public-muted { color: #718078; }
        .public-price { margin-top: 22px; color: #176b55; font-size: 25px; font-weight: 800; }
        .public-price small { color: #718078; font-size: 13px; font-weight: 500; }
        .public-facts { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 17px; }
        .public-chip { padding: 8px 11px; border: 1px solid #e1e9e3; border-radius: 999px; background: white; color: #53675c; font-size: 12px; }
        .public-description { margin-top: 24px; color: #42584a; line-height: 1.8; white-space: pre-line; }
        .public-contact { margin-top: 27px; padding: 20px; border: 1px solid #dcebe1; border-radius: 17px; background: #eef7f1; }
        .public-contact a { display: inline-flex; margin-top: 12px; padding: 11px 16px; border-radius: 9px; color: white; background: #176b55; text-decoration: none; font-weight: 700; }
        @media (max-width: 760px) { .public-detail-grid { grid-template-columns: 1fr; gap: 23px; } .public-container { margin-top: 24px; } }
    </style>
</head>
<body>
<header class="public-header">
    <div class="public-header-inner">
        <a href="{{ route('home') }}" class="public-logo">PANGO<span>.</span></a>
        <div style="display:flex;gap:18px;align-items:center">
            <a href="{{ route('home') }}#properties" class="public-home-link">Properties</a>
            <a href="{{ route('home') }}#vacancies" class="public-home-link">Vacancies</a>
        </div>
    </div>
</header>
@yield('content')
</body>
</html>
