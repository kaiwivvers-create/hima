<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard')</title>
    <style>
        :root {
            --ink: #2a2100;
            --muted: #5b4b18;
            --bg: #fff4b5;
            --card: #fff7d1;
            --line: rgba(42,33,0,.16);
            --accent: #2a2100;
            --danger: #9a2b00;
            --success-bg: #eaf9d7;
            --success-line: #5f8f1f;
            --error-bg: #ffe5dc;
            --error-line: #b2401f;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "DM Sans", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg);
            color: var(--ink);
        }

        .app {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 260px 1fr;
        }

        .sidebar {
            border-right: 1px solid var(--line);
            background: linear-gradient(180deg, #fff1ab 0%, #ffe996 100%);
            padding: 1rem;
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .brand {
            font-size: 1.05rem;
            font-weight: 800;
            margin: 0 0 1rem;
        }

        .nav {
            display: grid;
            gap: .45rem;
        }

        .nav-link {
            display: block;
            padding: .62rem .7rem;
            border-radius: 10px;
            border: 1px solid transparent;
            color: var(--ink);
            text-decoration: none;
            font-weight: 700;
            font-size: .95rem;
        }

        .nav-link:hover {
            border-color: var(--line);
            background: rgba(255,255,255,.55);
        }

        .nav-link.active {
            border-color: var(--accent);
            background: #fff7ce;
        }

        .main {
            padding: 1rem 1.25rem 1.25rem;
        }

        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .8rem;
            margin-bottom: .95rem;
        }

        .top h1 {
            margin: 0;
            font-size: 1.4rem;
            line-height: 1.2;
        }

        .top-right {
            display: flex;
            align-items: center;
            gap: .6rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .language-select,
        .btn,
        .btn-outline,
        input,
        select,
        textarea {
            padding: .52rem .72rem;
            border-radius: 8px;
            border: 1px solid var(--line);
            background: #fff9dc;
            color: var(--ink);
            font: inherit;
            text-decoration: none;
        }

        .btn,
        .btn-outline {
            font-weight: 700;
            cursor: pointer;
        }

        .btn {
            border-color: var(--accent);
            background: var(--accent);
            color: #fff7ce;
        }

        .btn-danger {
            border-color: var(--danger);
            background: var(--danger);
            color: #fff7ce;
        }

        .page-actions {
            display: flex;
            justify-content: flex-end;
            margin-bottom: .8rem;
            gap: .5rem;
        }

        .grid {
            display: grid;
            gap: .85rem;
            grid-template-columns: repeat(12, minmax(0, 1fr));
        }

        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: .95rem;
        }

        .muted { color: var(--muted); }

        .kpi { grid-column: span 3; }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: .45rem;
            font-size: .94rem;
        }

        .table th,
        .table td {
            text-align: left;
            padding: .52rem .4rem;
            border-bottom: 1px dashed rgba(42,33,0,.18);
            vertical-align: top;
        }

        .table th { font-size: .86rem; }

        .actions {
            display: flex;
            gap: .35rem;
            flex-wrap: wrap;
        }

        .field { margin-bottom: .7rem; }
        .field label { display: block; font-weight: 700; font-size: .92rem; margin-bottom: .25rem; }
        .field input,
        .field select,
        .field textarea { width: 100%; }

        .error { color: #8c1f00; font-size: .86rem; margin-top: .2rem; }

        .alert {
            margin-bottom: .8rem;
            padding: .65rem .75rem;
            border-radius: 10px;
            border: 1px solid;
            font-weight: 600;
        }

        .alert-success {
            background: var(--success-bg);
            border-color: var(--success-line);
        }

        .alert-error {
            background: var(--error-bg);
            border-color: var(--error-line);
        }

        .pagination { margin-top: .8rem; }

        @media (max-width: 920px) {
            .app { grid-template-columns: 1fr; }
            .sidebar {
                position: static;
                height: auto;
                border-right: none;
                border-bottom: 1px solid var(--line);
            }
            .kpi { grid-column: span 12; }
        }
    </style>
</head>
<body>
    @php
        $currentRoute = request()->route()?->getName() ?? '';
        $lang = app()->getLocale();
        $withLang = static fn (string $routeName, array $params = []): string => route($routeName, array_merge($params, ['lang' => $lang]));
    @endphp

    <div class="app">
        <aside class="sidebar">
            <p class="brand">Student Portal</p>
            <nav class="nav">
                <a href="{{ $withLang('dashboard') }}" class="nav-link {{ $currentRoute === 'dashboard' ? 'active' : '' }}">Overview</a>
                <a href="{{ $withLang('dashboard.attendances.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'dashboard.attendances.') ? 'active' : '' }}">Attendance</a>
                <a href="{{ $withLang('dashboard.payments.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'dashboard.payments.') ? 'active' : '' }}">Payments</a>
                <a href="{{ $withLang('dashboard.absences.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'dashboard.absences.') ? 'active' : '' }}">Absence Notes</a>
                <a href="{{ $withLang('dashboard.students.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'dashboard.students.') ? 'active' : '' }}">Students</a>
            </nav>
        </aside>

        <main class="main">
            <div class="top">
                <h1>@yield('page_title', 'Dashboard')</h1>
                <div class="top-right">
                    <label for="lang-select" style="display:none;">Language</label>
                    <select id="lang-select" class="language-select" aria-label="Language">
                        <option value="en" @selected(app()->getLocale() === 'en')>English</option>
                        <option value="id" @selected(app()->getLocale() === 'id')>Bahasa Indonesia</option>
                        <option value="zh" @selected(app()->getLocale() === 'zh')>Chinese</option>
                    </select>
                    <span class="btn-outline">{{ auth()->user()->name }} ({{ auth()->user()->role }})</span>
                    <form method="POST" action="{{ route('logout', ['lang' => app()->getLocale()]) }}">
                        @csrf
                        <button type="submit" class="btn">Log out</button>
                    </form>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error">Please check the form fields and try again.</div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        (function () {
            const select = document.getElementById('lang-select');
            if (!select) return;

            select.addEventListener('change', function () {
                const nextUrl = new URL(window.location.href);
                nextUrl.searchParams.set('lang', this.value);
                window.location.href = nextUrl.toString();
            });
        })();
    </script>
</body>
</html>
