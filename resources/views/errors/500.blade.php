@php
    $lang = app()->getLocale();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $lang) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('errors.500_title') }}</title>
    <style>
        :root { --ink:#2a2100; --bg:#fff4b5; --card:#fff7d1; --line: rgba(42,33,0,.16); }
        * { box-sizing: border-box; }
        body { margin:0; font-family:"DM Sans","Segoe UI",Tahoma,Geneva,Verdana,sans-serif; background:var(--bg); color:var(--ink); min-height:100vh; display:grid; place-items:center; padding:2rem; }
        .card { width:min(720px,100%); background:var(--card); border:1px solid var(--line); border-radius:16px; padding:2rem; box-shadow:0 14px 35px rgba(0,0,0,.15); position:relative; overflow:hidden; }
        .badge { display:inline-flex; align-items:center; gap:.5rem; padding:.35rem .75rem; border-radius:999px; background:#ffd1bf; border:1px solid rgba(154,43,0,.3); font-weight:700; color:#8a2b00; }
        h1 { margin:.6rem 0; font-size:1.8rem; }
        p { margin:.4rem 0; }
        .muted { color:#5b4b18; }
        .actions { display:flex; gap:.6rem; margin-top:1rem; flex-wrap:wrap; }
        .btn { padding:.6rem .9rem; border-radius:10px; border:1px solid #2a2100; background:#2a2100; color:#fff7ce; font-weight:700; text-decoration:none; }
        .btn-outline { padding:.6rem .9rem; border-radius:10px; border:1px solid rgba(42,33,0,.3); background:#fff9dc; color:#2a2100; font-weight:700; text-decoration:none; }
        .shape { position:absolute; left:-60px; top:-60px; width:180px; height:180px; background:radial-gradient(circle at 30% 30%, #ffd9c7, #ffc2a3); border-radius:40%; opacity:.6; }
    </style>
</head>
<body>
    <div class="card">
        <div class="shape"></div>
        <div class="badge">500</div>
        <h1>{{ __('errors.500_heading') }}</h1>
        <p class="muted">{{ __('errors.500_detail') }}</p>
        @if (isset($exception) && $exception?->getMessage())
            <p class="muted" style="margin-top:.6rem;font-size:.9rem;">
                {{ __('errors.error_reason') }} {{ $exception->getMessage() }}
            </p>
        @endif
        <div class="actions">
            <a class="btn" href="{{ url('/dashboard') }}">{{ __('errors.back_dashboard') }}</a>
            <a class="btn-outline" href="{{ url('/') }}">{{ __('errors.back_home') }}</a>
        </div>
    </div>
</body>
</html>
