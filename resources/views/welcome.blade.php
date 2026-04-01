<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $appName ?? config('app.name', 'Student Portal') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --yellow-1: #ffd84d;
            --yellow-2: #ffca1b;
            --yellow-3: #f1b500;
            --ink: #2a2100;
            --card: #fff7d1;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            overflow-x: hidden;
        }

        body {
            margin: 0;
            font-family: "DM Sans", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--ink);
            background-color: #fff4b5;
            min-height: 100vh;
        }

        .container {
            position: relative;
            width: min(980px, 92%);
            margin: 0 auto;
            padding: 1.25rem 0 3rem;
        }

        .container::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            z-index: 3;
            opacity: 0.42;
            background-image:
                radial-gradient(circle, rgba(255, 255, 255, 0.8) 0 1.25px, transparent 1.95px),
                radial-gradient(circle, rgba(255, 255, 255, 0.55) 0 1px, transparent 1.75px);
            background-size: 108px 108px, 162px 162px;
            background-position: 0 0, 42px 54px;
            animation: pageStarsDrift 38s linear infinite;
        }

        .container > * {
            position: relative;
            z-index: 2;
        }

        .topbar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 6;
            padding: 1rem 1.25rem 0;
        }

        .actions {
            display: flex;
            align-items: center;
            margin-left: auto;
            gap: 0.6rem;
        }

        .language-select {
            padding: 0.55rem 0.85rem;
            border-radius: 8px;
            border: 1px solid rgba(42, 33, 0, 0.2);
            background: rgba(255, 255, 255, 0.9);
            color: var(--ink);
            font-weight: 600;
            font-size: 0.9rem;
            outline: none;
        }

        .btn {
            padding: 0.55rem 1.15rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            border: 1px solid rgba(42, 33, 0, 0.2);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
        }

        .btn svg {
            width: 13px;
            height: 13px;
            fill: currentColor;
        }

        .btn-light {
            background: rgba(255, 255, 255, 0.7);
            color: var(--ink);
        }

        .btn-dark {
            background: var(--ink);
            color: #fff7ce;
            border-color: var(--ink);
        }

        .btn-login {
            background: #fff;
            border: 1px solid #fff;
            color: #2a2100;
        }

        .btn-register {
            background: #fff;
            border: 1px solid #fff;
            color: #2a2100;
        }

        .hero {
            position: relative;
            width: 100%;
            margin-left: 0;
            margin-right: 0;
            margin-top: -1px;
            margin-bottom: -2px;
            padding: 0;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(80, 55, 0, 0.15);
            background:
                linear-gradient(rgba(255, 226, 102, 0.45), rgba(255, 208, 41, 0.45)),
                url('{{ asset('images/hero-bg.jpg') }}') center/cover no-repeat;
        }

        .hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(255, 248, 214, 0.5);
            backdrop-filter: blur(1px);
        }

        .hero-content {
            position: relative;
            z-index: 2;
            display: grid;
            place-items: center;
            text-align: center;
            gap: 0;
            width: 100%;
            margin: 0 auto;
        }

        .hero-panel {
            position: relative;
            width: 100%;
            border-radius: 0;
            padding: 4.6rem 1.2rem 2.2rem;
            overflow: hidden;
            border-top: 1px solid rgba(255, 255, 255, 0.45);
            border-bottom: 1px solid rgba(255, 255, 255, 0.45);
            box-shadow: 0 8px 24px rgba(42, 33, 0, 0.16);
        }

        .hero-panel-bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: blur(4px);
            transform: scale(1.05);
            z-index: 0;
        }

        /* TRANSPARENT OVERLAY ABOVE THE IMAGE */
        .hero-panel::after {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.2);
            z-index: 0;
        }

        @keyframes pageStarsDrift {
            from { background-position: 0 0, 42px 54px; }
            to { background-position: -150px -96px, -50px -22px; }
        }

        .hero-panel-content {
            position: relative;
            z-index: 1;
            display: grid;
            place-items: center;
            text-align: center;
            gap: 1rem;
            width: min(980px, 92%);
            margin: 0 auto;
        }

        .hero-foreground {
            width: min(160px, 45vw);
            aspect-ratio: 1 / 1;
            border-radius: 18px;
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, 0.85);
            box-shadow: 0 10px 25px rgba(42, 33, 0, 0.18);
            background: #ffe59d;
        }

        h1 {
            margin: 0;
            font-family: "Plus Jakarta Sans", "DM Sans", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-size: clamp(1.5rem, 4vw, 2.4rem);
            line-height: 1.2;
            letter-spacing: 0.2px;
        }

        .lead {
            margin: 0;
            max-width: 700px;
            font-size: 1rem;
            font-weight: 500;
        }

        .grid {
            margin-top: 1.4rem;
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.9rem;
        }

        .card {
            background: var(--card);
            border-radius: 16px;
            padding: 1.05rem 1.15rem;
            border: 1px solid rgba(42, 33, 0, 0.12);
            box-shadow: 0 4px 14px rgba(60, 40, 0, 0.08);
        }

        .card h2 {
            margin: 0 0 0.45rem;
            font-family: "Plus Jakarta Sans", "DM Sans", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-size: 1.05rem;
            letter-spacing: 0.15px;
        }

        .card p {
            margin: 0;
            font-size: 0.92rem;
        }

        .person-row,
        .map-row {
            margin-top: 0.9rem;
            background: var(--card);
            border: 1px solid rgba(42, 33, 0, 0.12);
            border-radius: 16px;
            box-shadow: 0 4px 14px rgba(60, 40, 0, 0.08);
            padding: 1.1rem;
        }

        .person-content {
            display: grid;
            grid-template-columns: 170px 1fr;
            gap: 1rem;
            align-items: center;
        }

        .person-image {
            width: 100%;
            aspect-ratio: 1 / 1;
            border-radius: 14px;
            object-fit: cover;
            border: 2px solid rgba(42, 33, 0, 0.12);
            background: #ffe59d;
        }

        .person-text h2,
        .map-row h2 {
            margin: 0 0 0.45rem;
            font-family: "Plus Jakarta Sans", "DM Sans", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-size: 1.05rem;
            letter-spacing: 0.15px;
        }

        .person-text p,
        .map-row p {
            margin: 0;
            font-size: 0.92rem;
        }

        .map-frame {
            margin-top: 0.75rem;
            width: 100%;
            height: 320px;
            border: 0;
            border-radius: 12px;
        }

        .contact-row {
            margin-top: 0.9rem;
            background: var(--card);
            border: 1px solid rgba(42, 33, 0, 0.2);
            border-radius: 16px;
            box-shadow: 0 4px 14px rgba(60, 40, 0, 0.08);
            padding: 1.1rem;
        }

        .contact-bar {
            width: 100%;
            margin-left: 0;
            margin-right: 0;
            margin-top: 1rem;
            background: #fff0a1;
            border-top: 1px solid rgba(42, 33, 0, 0.2);
            border-bottom: 1px solid rgba(42, 33, 0, 0.2);
            padding: 1rem 1.2rem;
        }

        .contact-inner {
            width: min(980px, 92%);
            margin: 0 auto;
        }

        .contact-row h2 {
            margin: 0 0 0.6rem;
            font-family: "Plus Jakarta Sans", "DM Sans", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-size: 1.05rem;
            letter-spacing: 0.15px;
        }

        .contact-list {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 1.2rem;
            font-size: 0.94rem;
        }

        .contact-list a {
            color: #5f4300;
            text-decoration: none;
            font-weight: 600;
        }

        .contact-item {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            white-space: nowrap;
        }

        .contact-icon {
            width: 18px;
            height: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            background: #ffdf74;
            color: #4a3400;
            border: 1px solid rgba(42, 33, 0, 0.16);
        }

        .contact-icon svg {
            width: 12px;
            height: 12px;
            display: block;
            fill: currentColor;
        }

        @media (max-width: 860px) {
            .hero {
                padding: 0;
            }

            .person-content {
                grid-template-columns: 1fr;
            }

            .topbar {
                padding: 0.8rem 0.8rem 0;
            }

            .hero-panel {
                padding: 4rem 0.9rem 1.8rem;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
            <div class="actions">
                <label for="lang-select" style="display:none;">Language</label>
                <select id="lang-select" class="language-select" aria-label="Select language">
                    <option value="en" @selected(app()->getLocale() === 'en')>English</option>
                    <option value="id" @selected(in_array(app()->getLocale(), ['id', 'in'], true))>Bahasa Indonesia</option>
                    <option value="zh" @selected(app()->getLocale() === 'zh')>中文</option>
                </select>
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-dark">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h8v8H3V3zm10 0h8v5h-8V3zM3 13h5v8H3v-8zm7 0h11v8H10v-8z"/></svg>
                            {{ $appText['welcome_nav_dashboard'] ?? __('welcome.nav_dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-login">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 17v-3h8v-4h-8V7l-5 5 5 5zm3 5H7a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h7v2H7v16h7v2z"/></svg>
                            {{ $appText['welcome_nav_login'] ?? __('welcome.nav_login') }}
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-register">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 13a4 4 0 1 0-4-4 4 4 0 0 0 4 4zm-8 7v-1.2a4.8 4.8 0 0 1 4.8-4.8h6.4A4.8 4.8 0 0 1 23 18.8V20h-2v-1.2a2.8 2.8 0 0 0-2.8-2.8h-6.4A2.8 2.8 0 0 0 9 18.8V20H7zm-6-9h5v2H1v-2zm0-4h7v2H1V7z"/></svg>
                                {{ $appText['welcome_nav_register'] ?? __('welcome.nav_register') }}
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
    </header>

    <section class="hero">
        <div class="hero-content">
            <!-- FULL-WIDTH WELCOME PANEL -->
            <div class="hero-panel">
                <img class="hero-panel-bg" src=
                {{-- {{ asset('https://images.cara.app/production/posts/e78a3ca9-f599-446e-8717-9f8e27263383/octopie-B2P83Y42IECMIj_cmPTDt-0C32E370-17E0-422E-8280-F1B1148145FB.jpg?width=1920') }}"  --}}
                alt="Welcome Panel Background">
                <div class="hero-panel-content">
                    @if (!empty($appLogoUrl))
                        <img class="hero-foreground" src="{{ $appLogoUrl }}" alt="App Logo">
                    @else
                        <div class="hero-foreground" style="display:grid;place-items:center;font-weight:800;color:#2a2100;">
                            {{ strtoupper(substr($appName ?? 'SP', 0, 1)) }}
                        </div>
                    @endif
                    <h1>{{ $appName ?? __('welcome.hero_title') }}</h1>
                    <p class="lead">{{ $appText['welcome_hero_description'] ?? __('welcome.hero_description') }}</p>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <section class="grid">
            <article class="card">
                <h2>{{ $appText['welcome_section_1_title'] ?? __('welcome.section_1_title') }}</h2>
                <p>{{ $appText['welcome_section_1_body'] ?? __('welcome.section_1_body') }}</p>
            </article>
            <article class="card">
                <h2>{{ $appText['welcome_section_2_title'] ?? __('welcome.section_2_title') }}</h2>
                <p>{{ $appText['welcome_section_2_body'] ?? __('welcome.section_2_body') }}</p>
            </article>
            <article class="card">
                <h2>{{ $appText['welcome_section_3_title'] ?? __('welcome.section_3_title') }}</h2>
                <p>{{ $appText['welcome_section_3_body'] ?? __('welcome.section_3_body') }}</p>
            </article>
        </section>

        <section class="person-row">
            <div class="person-content">
                <img class="person-image" src="{{ asset('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRaiS4ISNcvVQGuwoLhh_68R_ylknNfb6UzMg&s') }}" alt="Person Image">
                <div class="person-text">
                    <h2>{{ $appText['welcome_person_title'] ?? __('welcome.person_title') }}</h2>
                    <p>{{ $appText['welcome_person_body'] ?? __('welcome.person_body') }}</p>
                </div>
            </div>
        </section>

        <section class="map-row">
            <h2>{{ $appText['welcome_map_title'] ?? __('welcome.map_title') }}</h2>
            <p>{{ $appText['welcome_map_body'] ?? __('welcome.map_body') }}</p>
            <iframe
                class="map-frame"
                loading="lazy"
                allowfullscreen
                referrerpolicy="no-referrer-when-downgrade"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d249.3152558732788!2d104.00918984493318!3d1.1287612014249784!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31d98bd8817a7467%3A0x3a20c8cbb73f0e6d!2sHimawari%20Education%20Batam!5e0!3m2!1sen!2sid!4v1774405682752!5m2!1sen!2sid">
            </iframe>
        </section>

    </div>

    <section class="contact-bar">
        <div class="contact-inner">
            <div class="contact-row" style="margin:0;">
                <div class="contact-list">
                    <div class="contact-item">
                        <span class="contact-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" role="img">
                                <path d="M1.5 4.5h21v15h-21v-15zm1.8 1.5v.6L12 12.9l8.7-6.3V6H3.3zm17.4 12v-9.3L12 15l-8.7-6.3V18h17.4z"/>
                            </svg>
                        </span>
                        <a href="mailto:kaiwivvers@gmail.com">hello@example.com</a>
                    </div>
                    <div class="contact-item">
                        <span class="contact-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" role="img">
                                <path d="M20.52 3.48A11.86 11.86 0 0 0 12.07 0C5.53 0 .2 5.32.2 11.86c0 2.1.55 4.14 1.6 5.94L0 24l6.4-1.67a11.8 11.8 0 0 0 5.67 1.44h.01c6.54 0 11.86-5.32 11.86-11.86 0-3.17-1.23-6.14-3.42-8.43zM12.08 21.7h-.01a9.82 9.82 0 0 1-5-1.37l-.36-.21-3.8.99 1.01-3.71-.23-.38a9.8 9.8 0 0 1-1.5-5.17c0-5.43 4.42-9.85 9.86-9.85 2.64 0 5.12 1.03 6.98 2.89a9.8 9.8 0 0 1 2.88 6.99c0 5.44-4.42 9.86-9.83 9.86zm5.4-7.37c-.3-.15-1.78-.88-2.06-.98-.28-.1-.48-.15-.68.15-.2.3-.78.98-.95 1.18-.18.2-.35.23-.65.08-.3-.15-1.27-.47-2.42-1.5-.9-.8-1.5-1.78-1.67-2.08-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.53.15-.18.2-.3.3-.5.1-.2.05-.38-.02-.53-.08-.15-.68-1.64-.93-2.24-.25-.6-.5-.52-.68-.53h-.58c-.2 0-.53.08-.8.38-.28.3-1.06 1.03-1.06 2.52 0 1.48 1.08 2.92 1.23 3.12.15.2 2.12 3.23 5.13 4.53.72.31 1.28.5 1.72.64.72.23 1.37.2 1.88.12.57-.08 1.78-.73 2.03-1.43.25-.7.25-1.3.18-1.43-.08-.13-.28-.2-.58-.35z"/>
                            </svg>
                        </span>
                        <a href="https://wa.me/6285363410088" target="_blank" rel="noopener noreferrer">+62 812-3456-7890</a>
                    </div>
                    <div class="contact-item">
                        <span class="contact-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" role="img">
                                <path d="M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2zm-.12 1.9A3.73 3.73 0 0 0 3.9 7.63v8.74a3.73 3.73 0 0 0 3.73 3.73h8.74a3.73 3.73 0 0 0 3.73-3.73V7.63a3.73 3.73 0 0 0-3.73-3.73H7.63zm9.62 1.46a1.19 1.19 0 1 1 0 2.38 1.19 1.19 0 0 1 0-2.38zM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10zm0 1.9A3.1 3.1 0 1 0 12 15.1 3.1 3.1 0 0 0 12 8.9z"/>
                            </svg>
                        </span>
                        <a href="https://instagram.com/octo__pie" target="_blank" rel="noopener noreferrer">@yourusername</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        (function () {
            const select = document.getElementById('lang-select');
            if (!select) return;

            const applyLanguage = function () {
                const nextParams = new URLSearchParams(window.location.search);
                nextParams.set('lang', this.value);
                window.location.search = nextParams.toString();
            };

            select.addEventListener('input', applyLanguage);
            select.addEventListener('change', applyLanguage);
        })();
    </script>
</body>
</html>
