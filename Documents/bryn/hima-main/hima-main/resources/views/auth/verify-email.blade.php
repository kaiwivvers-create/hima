<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('auth.verify_email_title') }}</title>
    <style>
        body { margin:0; font-family: "DM Sans", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background:#fff4b5; color:#2a2100; padding:3rem 0; }
        .wrap { width:min(620px,92%); margin:0 auto; }
        .card { background:#fff7d1; border:1px solid rgba(42,33,0,.15); border-radius:14px; padding:1.35rem 1.25rem; box-shadow:0 8px 24px rgba(42,33,0,.1); }
        h1 { margin:0 0 .8rem; font-size:1.35rem; }
        .muted { color:#5b4b18; }
        .actions { display:flex; gap:.5rem; flex-wrap:wrap; margin-top:1rem; }
        .btn, .btn-outline { padding:.65rem .9rem; border-radius:8px; font-weight:700; cursor:pointer; text-decoration:none; border:1px solid #2a2100; font:inherit; }
        .btn { background:#2a2100; color:#fff7ce; }
        .btn-outline { background:#fff9dc; color:#2a2100; }
        .alert { margin:.8rem 0; padding:.65rem .75rem; border-radius:10px; border:1px solid #5f8f1f; background:#eaf9d7; font-weight:600; }
    </style>
</head>
<body>
    <div class="wrap">
        <section class="card">
            <h1>{{ __('auth.verify_email_title') }}</h1>
            <p class="muted">{{ __('auth.verify_email_message') }}</p>

            @if (session('success'))
                <div class="alert">{{ session('success') }}</div>
            @endif

            @if (session('status') === 'verification-link-sent')
                <div class="alert">{{ __('auth.verify_email_resent') }}</div>
            @endif

            <div class="actions">
                <form method="POST" action="{{ route('verification.send', ['lang' => app()->getLocale()]) }}">
                    @csrf
                    <button type="submit" class="btn">{{ __('auth.verify_email_resend') }}</button>
                </form>
                <a class="btn-outline" href="{{ route('login', ['lang' => app()->getLocale()]) }}">{{ __('auth.top_login') }}</a>
            </div>
        </section>
    </div>
</body>
</html>
