<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root { --ink: #2a2100; --card: #fff7d1; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: "DM Sans", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background: #fff4b5; color: var(--ink); padding: 3rem 0; }
        .container { width: min(620px, 92%); margin: 0 auto; }
        .card { background: var(--card); border: 1px solid rgba(42,33,0,.15); border-radius: 14px; padding: 1.35rem 1.25rem; box-shadow: 0 8px 24px rgba(42,33,0,.1); }
        h1 { margin: 0 0 .8rem; font-family: "Plus Jakarta Sans", "DM Sans", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; font-size: 1.35rem; }
        label { display: block; font-size: .9rem; margin-bottom: .25rem; }
        input { width: 100%; padding: .62rem .72rem; border-radius: 8px; border: 1px solid rgba(42,33,0,.2); margin-bottom: .85rem; font: inherit; background: #fffdf4; }
        .btn { width: 100%; padding: .7rem .95rem; border-radius: 8px; border: 1px solid #2a2100; background: #2a2100; color: #fff7ce; font-weight: 700; cursor: pointer; }
        .muted { margin-top: .85rem; font-size: .9rem; }
        a { color: #6c4d00; text-decoration: none; font-weight: 600; }
        .error { color: #8c1f00; font-size: .85rem; margin: -.6rem 0 .75rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Reset Password</h1>
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required>
                @error('email')<div class="error">{{ $message }}</div>@enderror

                <label for="password">New Password</label>
                <input id="password" name="password" type="password" minlength="6" required>
                @error('password')<div class="error">{{ $message }}</div>@enderror

                <label for="password_confirmation">Confirm Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" minlength="6" required>

                <button class="btn" type="submit">Reset Password</button>
            </form>
            <p class="muted"><a href="{{ route('login') }}">Back to login</a></p>
        </div>
    </div>
</body>
</html>
