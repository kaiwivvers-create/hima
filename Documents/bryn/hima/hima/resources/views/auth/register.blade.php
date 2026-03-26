<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('auth.register_title') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root { --ink: #2a2100; --card: #fff7d1; }
        * { box-sizing: border-box; }
        html, body { overflow-x: hidden; }
        body { margin: 0; font-family: "DM Sans", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background: #fff4b5; color: var(--ink); padding-bottom: 72px; }

        .topbar { display: flex; justify-content: flex-end; align-items: center; width: 100%; position: absolute; top: 0; left: 0; z-index: 6; padding: 1rem 1.25rem 0; }
        .actions { display: flex; align-items: center; margin-left: auto; gap: 0.6rem; }
        .top-link { padding: 0.55rem 1.05rem; border-radius: 8px; border: 1px solid rgba(42,33,0,.2); text-decoration: none; font-weight: 700; font-size: .9rem; background: rgba(255,255,255,.86); color: var(--ink); display: inline-flex; align-items: center; gap: .4rem; }
        .top-link svg { width: 13px; height: 13px; fill: currentColor; }
        .language-select { padding: 0.55rem 0.85rem; border-radius: 8px; border: 1px solid rgba(42, 33, 0, 0.2); background: rgba(255, 255, 255, 0.9); color: var(--ink); font-weight: 600; font-size: 0.9rem; outline: none; }

        .hero { position: relative; width: 100%; margin-top: -1px; margin-bottom: -2px; padding: 0; overflow: hidden; box-shadow: 0 10px 30px rgba(80,55,0,.15); background: linear-gradient(rgba(255,226,102,.45), rgba(255,208,41,.45)), url('{{ asset('images/hero-bg.jpg') }}') center/cover no-repeat; }
        .hero::before { content: ""; position: absolute; inset: 0; background: rgba(255,248,214,.5); backdrop-filter: blur(1px); }
        .hero-content { position: relative; z-index: 2; width: 100%; max-width: 980px; margin: 0 auto; text-align: center; display: grid; justify-items: center; padding: 4.8rem 0 2.1rem; }
        .hero h1 { margin: 0; text-align: center; font-family: "Plus Jakarta Sans", "DM Sans", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; font-size: clamp(1.4rem, 4vw, 2rem); }
        .hero p { margin: .45rem 0 0; text-align: center; font-weight: 500; }

        .container { width: min(980px, 92%); margin: 0 auto; padding: 1.25rem 0 3rem; }
        .auth-wrap { display: grid; place-items: center; }
        .card { width: min(560px, 100%); background: var(--card); border: 1px solid rgba(42,33,0,.15); border-radius: 14px; padding: 1.35rem 1.25rem; box-shadow: 0 8px 24px rgba(42,33,0,.1); }
        .card h2 { margin: 0 0 .95rem; font-family: "Plus Jakarta Sans", "DM Sans", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; font-size: 1.25rem; }
        label { display: block; font-size: .9rem; margin-bottom: .25rem; }
        input, select { width: 100%; padding: .62rem .72rem; border-radius: 8px; border: 1px solid rgba(42,33,0,.2); margin-bottom: .85rem; font: inherit; background: #fffdf4; }
        .btn { width: 100%; padding: .7rem .95rem; border-radius: 8px; border: 1px solid #2a2100; background: #2a2100; color: #fff7ce; font-weight: 700; cursor: pointer; }
        .btn-content { display: inline-flex; align-items: center; gap: .42rem; }
        .btn-content svg { width: 14px; height: 14px; fill: currentColor; }
        .muted { margin-top: .85rem; font-size: .9rem; }
        a { color: #6c4d00; text-decoration: none; font-weight: 600; }
        .error { color: #8c1f00; font-size: .85rem; margin: -.6rem 0 .75rem; }

        .contact-bar { width: 100%; position: fixed; left: 0; bottom: 0; z-index: 10; margin-top: 0; background: #fff0a1; border-top: 1px solid rgba(42,33,0,.2); border-bottom: 1px solid rgba(42,33,0,.2); padding: 1rem 1.2rem; }
        .contact-inner { width: min(980px, 92%); margin: 0 auto; }
        .contact-list { display: flex; align-items: center; justify-content: center; flex-wrap: wrap; gap: 1.2rem; font-size: .94rem; }
        .contact-item { display: inline-flex; align-items: center; gap: 0.4rem; white-space: nowrap; }
        .contact-icon { width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; background: #ffdf74; color: #4a3400; border: 1px solid rgba(42, 33, 0, 0.16); }
        .contact-icon svg { width: 12px; height: 12px; display: block; fill: currentColor; }

        @media (max-width: 720px) {
            .hero-content { padding-top: 4.2rem; }
            .card { padding: 1.1rem .95rem; }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="actions">
            <label for="lang-select" style="display:none;">{{ __('auth.lang_label') }}</label>
            <select id="lang-select" class="language-select" aria-label="{{ __('auth.lang_label') }}">
                <option value="en" @selected(app()->getLocale() === 'en')>{{ __('auth.lang_en') }}</option>
                <option value="id" @selected(app()->getLocale() === 'id')>{{ __('auth.lang_id') }}</option>
                <option value="zh" @selected(app()->getLocale() === 'zh')>{{ __('auth.lang_zh') }}</option>
            </select>
            <a class="top-link" href="{{ url('/') }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3.2 2.8 11h2.6v9.8h5.7v-6h1.8v6h5.7V11h2.6L12 3.2z"/></svg>
                {{ __('auth.top_home') }}
            </a>
            <a class="top-link" href="{{ route('login') }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 17v-3h8v-4h-8V7l-5 5 5 5zm3 5H7a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h7v2H7v16h7v2z"/></svg>
                {{ __('auth.top_login') }}
            </a>
        </div>
    </header>

    <section class="hero">
        <div class="hero-content">
            <h1>{{ __('auth.register_hero_title') }}</h1>
            <p>{{ __('auth.register_hero_text') }}</p>
        </div>
    </section>

    <div class="container">
        <section class="auth-wrap">
            <form method="POST" action="{{ route('register.post') }}" class="card">
                @csrf
                <h2>{{ __('auth.register_title') }}</h2>

                <label for="name">{{ __('auth.name') }}</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus>
                @error('name')<div class="error">{{ $message }}</div>@enderror

                <label for="email">{{ __('auth.email') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                @error('email')<div class="error">{{ $message }}</div>@enderror

                <label for="role">{{ __('auth.account_type') }}</label>
                <select id="role" name="role" required>
                    <option value="student" @selected(old('role', 'student') === 'student')>{{ __('auth.role_student') }}</option>
                    <option value="parent" @selected(old('role') === 'parent')>{{ __('auth.role_parent') }}</option>
                </select>
                @error('role')<div class="error">{{ $message }}</div>@enderror

                <label for="password">{{ __('auth.password') }}</label>
                <input id="password" name="password" type="password" required>
                @error('password')<div class="error">{{ $message }}</div>@enderror

                <label for="password_confirmation">{{ __('auth.password_confirm') }}</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required>

                <label for="captcha_answer">{{ __('auth.captcha') }}: {{ $captchaA ?? 0 }} + {{ $captchaB ?? 0 }} = ?</label>
                <input id="captcha_answer" name="captcha_answer" type="number" inputmode="numeric" value="{{ old('captcha_answer') }}" required>
                @error('captcha_answer')<div class="error">{{ $message }}</div>@enderror

                <button class="btn" type="submit">
                    <span class="btn-content">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 13a4 4 0 1 0-4-4 4 4 0 0 0 4 4zm-8 7v-1.2a4.8 4.8 0 0 1 4.8-4.8h6.4A4.8 4.8 0 0 1 23 18.8V20h-2v-1.2a2.8 2.8 0 0 0-2.8-2.8h-6.4A2.8 2.8 0 0 0 9 18.8V20H7zm-6-9h5v2H1v-2zm0-4h7v2H1V7z"/></svg>
                        {{ __('auth.create_account') }}
                    </span>
                </button>
                <p class="muted">{{ __('auth.have_account') }} <a href="{{ route('login') }}">{{ __('auth.top_login') }}</a></p>
            </form>
        </section>
    </div>

    <section class="contact-bar">
        <div class="contact-inner">
            <div class="contact-list">
                <div class="contact-item">
                    <span class="contact-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" role="img"><path d="M1.5 4.5h21v15h-21v-15zm1.8 1.5v.6L12 12.9l8.7-6.3V6H3.3zm17.4 12v-9.3L12 15l-8.7-6.3V18h17.4z"/></svg>
                    </span>
                    <a href="mailto:kaiwivvers@gmail.com">hello@example.com</a>
                </div>
                <div class="contact-item">
                    <span class="contact-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" role="img"><path d="M20.52 3.48A11.86 11.86 0 0 0 12.07 0C5.53 0 .2 5.32.2 11.86c0 2.1.55 4.14 1.6 5.94L0 24l6.4-1.67a11.8 11.8 0 0 0 5.67 1.44h.01c6.54 0 11.86-5.32 11.86-11.86 0-3.17-1.23-6.14-3.42-8.43zM12.08 21.7h-.01a9.82 9.82 0 0 1-5-1.37l-.36-.21-3.8.99 1.01-3.71-.23-.38a9.8 9.8 0 0 1-1.5-5.17c0-5.43 4.42-9.85 9.86-9.85 2.64 0 5.12 1.03 6.98 2.89a9.8 9.8 0 0 1 2.88 6.99c0 5.44-4.42 9.86-9.83 9.86zm5.4-7.37c-.3-.15-1.78-.88-2.06-.98-.28-.1-.48-.15-.68.15-.2.3-.78.98-.95 1.18-.18.2-.35.23-.65.08-.3-.15-1.27-.47-2.42-1.5-.9-.8-1.5-1.78-1.67-2.08-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.53.15-.18.2-.3.3-.5.1-.2.05-.38-.02-.53-.08-.15-.68-1.64-.93-2.24-.25-.6-.5-.52-.68-.53h-.58c-.2 0-.53.08-.8.38-.28.3-1.06 1.03-1.06 2.52 0 1.48 1.08 2.92 1.23 3.12.15.2 2.12 3.23 5.13 4.53.72.31 1.28.5 1.72.64.72.23 1.37.2 1.88.12.57-.08 1.78-.73 2.03-1.43.25-.7.25-1.3.18-1.43-.08-.13-.28-.2-.58-.35z"/></svg>
                    </span>
                    <a href="https://wa.me/6285363410088" target="_blank" rel="noopener noreferrer">+62 812-3456-7890</a>
                </div>
                <div class="contact-item">
                    <span class="contact-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" role="img"><path d="M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2zm-.12 1.9A3.73 3.73 0 0 0 3.9 7.63v8.74a3.73 3.73 0 0 0 3.73 3.73h8.74a3.73 3.73 0 0 0 3.73-3.73V7.63a3.73 3.73 0 0 0-3.73-3.73H7.63zm9.62 1.46a1.19 1.19 0 1 1 0 2.38 1.19 1.19 0 0 1 0-2.38zM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10zm0 1.9A3.1 3.1 0 1 0 12 15.1 3.1 3.1 0 0 0 12 8.9z"/></svg>
                    </span>
                    <a href="https://instagram.com/octo__pie" target="_blank" rel="noopener noreferrer">@yourusername</a>
                </div>
            </div>
        </div>
    </section>
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
