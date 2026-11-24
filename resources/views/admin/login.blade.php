<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator sign in | Pango</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/pango-glass.css') }}">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 20px; background: #f3f7f4; color: #17251f; font-family: 'DM Sans',sans-serif; }
        .admin-login { width: min(420px,100%); padding: 30px; border: 1px solid #e4ece6; border-radius: 18px; background: white; box-shadow: 0 20px 60px rgba(20,44,36,.08); }
        .admin-login h1 { margin: 0; font-size: 23px; }
        .admin-login p { margin: 8px 0 22px; color: #718078; font-size: 13px; }
        .admin-login label { display: grid; gap: 7px; margin: 15px 0; font-size: 12px; font-weight: 700; }
        .admin-login input { padding: 11px; border: 1px solid #dfe7e1; border-radius: 8px; font: inherit; }
        .admin-login button { width: 100%; padding: 12px; border: 0; border-radius: 8px; color: white; background: #176b55; font: inherit; font-weight: 700; cursor: pointer; }
        .admin-login .error { margin-bottom: 14px; color: #a83232; font-size: 12px; }
    </style>
</head>
<body>
<main class="admin-login">
    <h1>Administrator sign in</h1>
    <p>Restricted Pango operations portal.</p>
    @if($errors->any())<div class="error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
    <form method="POST" action="{{ route('admin.login.submit') }}">
        @csrf
        <label>Email<input type="email" name="email" required autocomplete="username" value="{{ old('email') }}"></label>
        <label>Password<input type="password" name="password" required autocomplete="current-password"></label>
        <button type="submit">Sign in securely</button>
    </form>
</main>
</body>
</html>
