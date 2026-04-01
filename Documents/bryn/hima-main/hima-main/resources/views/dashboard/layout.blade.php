<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('Dashboard Overview'))</title>
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

        .page-dim {
            position: fixed;
            inset: 0;
            background: rgba(12, 8, 0, .48);
            opacity: 0;
            pointer-events: none;
            z-index: 70;
            transition: opacity .28s ease;
        }

        body.modal-open .page-dim {
            opacity: 1;
        }

        .sidebar {
            border-right: 1px solid var(--line);
            background: linear-gradient(180deg, #fff1ab 0%, #ffe996 100%);
            padding: 1rem;
            position: sticky;
            top: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .brand {
            font-size: 1.05rem;
            font-weight: 800;
            margin: 0 0 1rem;
            display: flex;
            align-items: center;
            gap: .55rem;
        }

        .brand-logo {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            border: 1px solid var(--line);
            object-fit: cover;
            background: #fff7d1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .82rem;
            font-weight: 800;
        }

        .nav {
            display: grid;
            gap: .45rem;
            flex: 1;
            align-content: start;
        }

        .sidebar-greeting {
            margin: 0 0 1rem;
            padding: .7rem .8rem;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: rgba(255, 255, 255, .35);
        }

        .sidebar-footer {
            margin: .4rem -0.6rem 0;
            padding: .9rem .6rem .6rem;
            border-top: 1px dashed var(--line);
            display: flex;
            gap: .7rem;
            align-items: center;
            color: var(--ink);
            border-radius: 12px;
            text-decoration: none;
            background: transparent;
            border: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
        }

        .sidebar-footer:hover {
            color: var(--ink);
            background: rgba(255,255,255,.4);
        }

        .avatar {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid var(--line);
            background: #fff7d1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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

        .nav-link-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .45rem;
        }

        .nav-badge {
            min-width: 1.2rem;
            height: 1.2rem;
            padding: 0 .32rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .72rem;
            font-weight: 800;
            line-height: 1;
            background: #b51616;
            color: #fff;
            border: 1px solid rgba(255,255,255,.55);
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

        .page-reveal {
            opacity: 0;
            transform: translateY(22px);
            animation: rise-in .65s cubic-bezier(.2, .8, .2, 1) forwards;
            animation-play-state: paused;
        }

        body.page-ready .page-reveal {
            animation-play-state: running;
        }

        .page-reveal-delay-1 { animation-delay: .04s; }
        .page-reveal-delay-2 { animation-delay: .09s; }
        .page-reveal-delay-3 { animation-delay: .14s; }
        .page-reveal-delay-4 { animation-delay: .19s; }
        .page-reveal-delay-5 { animation-delay: .24s; }

        .page-animate > *:not(.modal) {
            opacity: 0;
            transform: translateY(22px);
            animation: rise-in .65s cubic-bezier(.2, .8, .2, 1) forwards;
            animation-play-state: paused;
        }

        body.page-ready .page-animate > *:not(.modal) {
            animation-play-state: running;
        }

        .page-animate > *:not(.modal):nth-child(1) { animation-delay: .12s; }
        .page-animate > *:not(.modal):nth-child(2) { animation-delay: .18s; }
        .page-animate > *:not(.modal):nth-child(3) { animation-delay: .24s; }
        .page-animate > *:not(.modal):nth-child(4) { animation-delay: .3s; }
        .page-animate > *:not(.modal):nth-child(5) { animation-delay: .36s; }

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

        .top-subtitle {
            margin: .2rem 0 0;
            font-size: .92rem;
            color: var(--muted);
            font-weight: 600;
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

        .modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 90;
            padding: 1rem;
        }

        .modal.active { display: flex; }

        .modal-backdrop {
            position: absolute;
            inset: 0;
            background: transparent;
            opacity: 0;
            transition: opacity .28s ease;
        }

        .modal-card {
            position: relative;
            z-index: 2;
            width: min(760px, 100%);
            max-height: 92vh;
            overflow: auto;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 1rem;
            box-shadow: 0 14px 35px rgba(0, 0, 0, .2);
            opacity: 0;
            transform: translateY(26px) scale(.985);
            transition: opacity .28s ease, transform .32s cubic-bezier(.2, .8, .2, 1);
        }

        .modal.active .modal-backdrop {
            opacity: 1;
        }

        .modal.active .modal-card {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .modal-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .8rem;
            margin-bottom: .8rem;
        }

        .modal-head h2 {
            margin: 0;
            font-size: 1.1rem;
        }

        .toast-stack {
            position: fixed;
            right: 1rem;
            bottom: 1rem;
            z-index: 120;
            display: flex;
            flex-direction: column;
            gap: .7rem;
            width: min(360px, calc(100vw - 2rem));
            pointer-events: none;
        }

        .toast {
            pointer-events: auto;
            background: #fff7d1;
            border: 1px solid var(--line);
            border-left: 5px solid var(--accent);
            border-radius: 14px;
            box-shadow: 0 12px 28px rgba(42, 33, 0, .18);
            padding: .9rem 1rem;
            opacity: 0;
            transform: translateY(14px);
            transition: opacity .25s ease, transform .25s ease;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .toast-title {
            margin: 0;
            font-size: .95rem;
            font-weight: 800;
        }

        .toast-body {
            margin: .3rem 0 0;
            color: var(--muted);
            font-size: .88rem;
            line-height: 1.45;
        }

        .image-editor-shell {
            display: grid;
            grid-template-columns: minmax(320px, 1fr) 260px;
            gap: 1rem;
        }

        .image-editor-stage {
            position: relative;
            min-height: 360px;
            border-radius: 16px;
            border: 1px solid var(--line);
            background:
                linear-gradient(45deg, rgba(42,33,0,.06) 25%, transparent 25%, transparent 75%, rgba(42,33,0,.06) 75%),
                linear-gradient(45deg, rgba(42,33,0,.06) 25%, transparent 25%, transparent 75%, rgba(42,33,0,.06) 75%);
            background-position: 0 0, 12px 12px;
            background-size: 24px 24px;
            overflow: hidden;
            cursor: grab;
            touch-action: none;
        }

        .image-editor-stage.dragging {
            cursor: grabbing;
        }

        .image-editor-canvas {
            width: 100%;
            height: 100%;
            display: block;
        }

        .image-editor-sidebar {
            display: flex;
            flex-direction: column;
            gap: .8rem;
        }

        .image-editor-box {
            padding: .8rem;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: rgba(255,255,255,.35);
        }

        .image-editor-box h3 {
            margin: 0 0 .6rem;
            font-size: .96rem;
        }

        .image-editor-controls {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
        }

        .image-editor-controls .btn-outline,
        .image-editor-controls .btn {
            padding: .45rem .7rem;
        }

        .image-editor-preview {
            width: 120px;
            height: 120px;
            border-radius: 20px;
            border: 1px solid var(--line);
            background: #fff7d1;
            overflow: hidden;
            margin: 0 auto;
        }

        .image-editor-preview canvas {
            width: 100%;
            height: 100%;
            display: block;
        }

        @keyframes rise-in {
            from {
                opacity: 0;
                transform: translateY(22px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 920px) {
            .app { grid-template-columns: 1fr; }
            .sidebar {
                position: static;
                height: auto;
                border-right: none;
                border-bottom: 1px solid var(--line);
            }

            .modal {
                align-items: center;
            }

            .image-editor-shell {
                grid-template-columns: 1fr;
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
        $sidebarUser = auth()->user();
        $sidebarAvatar = $sidebarUser?->avatar_path ? asset('storage/'.$sidebarUser->avatar_path) : null;
        $sidebarInitial = strtoupper(substr($sidebarUser?->name ?? 'U', 0, 1));
        $sidebarCanAll = $sidebarUser?->role === 'super admin';
        $unreadNotifications = 0;
        $sidebarPermissions = [];
        if (!$sidebarCanAll && $sidebarUser) {
            $roleId = \Illuminate\Support\Facades\DB::table('roles')->where('name', $sidebarUser->role)->value('id');
            if ($roleId) {
                $sidebarPermissions = \Illuminate\Support\Facades\DB::table('role_permission')
                    ->join('permissions', 'permissions.id', '=', 'role_permission.permission_id')
                    ->where('role_permission.role_id', $roleId)
                    ->pluck('permissions.name')
                    ->all();
            }
        }
        if ($sidebarUser) {
            $unreadNotifications = \Illuminate\Support\Facades\DB::table('user_notifications')
                ->where('user_id', $sidebarUser->id)
                ->whereNull('archived_at')
                ->whereNull('read_at')
                ->count();
        }
        $can = static fn (string $permission): bool => $sidebarCanAll || in_array($permission, $sidebarPermissions, true);
    @endphp

    <div class="app">
        <aside class="sidebar">
            <p class="brand">
                @if ($appLogoUrl)
                    <img src="{{ $appLogoUrl }}" alt="App Logo" class="brand-logo">
                @else
                    <span class="brand-logo">{{ strtoupper(substr($appName ?? 'SP', 0, 1)) }}</span>
                @endif
                <span>{{ $appName ?? 'Student Portal' }}</span>
            </p>
            <div class="sidebar-greeting">
                <div style="font-weight:800;font-size:1rem;line-height:1.1;">Welcome, {{ $sidebarUser?->name }}</div>
                <div class="muted" style="font-size:.85rem;margin-top:.2rem;">{{ ucfirst($sidebarUser?->role ?? 'user') }}</div>
            </div>
            <nav class="nav">
                @if ($can('dashboard.view'))
                    <a href="{{ $withLang('dashboard') }}" class="nav-link {{ $currentRoute === 'dashboard' ? 'active' : '' }}">{{ __('Overview') }}</a>
                @endif
                @if ($can('attendance.view'))
                    <a href="{{ $withLang('dashboard.attendances.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'dashboard.attendances.') ? 'active' : '' }}">{{ __('Attendance') }}</a>
                @endif
                @if ($can('payments.view'))
                    <a href="{{ $withLang('dashboard.payments.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'dashboard.payments.') ? 'active' : '' }}">{{ __('Payments') }}</a>
                @endif
                @if ($can('absences.view'))
                    <a href="{{ $withLang('dashboard.absences.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'dashboard.absences.') ? 'active' : '' }}">{{ __('Absence Notes') }}</a>
                @endif
                @if ($can('students.view'))
                    <a href="{{ $withLang('dashboard.students.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'dashboard.students.') ? 'active' : '' }}">{{ __('Students') }}</a>
                @endif
                @if ($can('users.view'))
                    <a href="{{ $withLang('dashboard.users.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'dashboard.users.') ? 'active' : '' }}">{{ __('Users') }}</a>
                @endif
                @if ($sidebarUser && in_array($sidebarUser->role, ['student', 'parent'], true))
                    <a href="{{ $withLang('dashboard.connections.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'dashboard.connections.') ? 'active' : '' }}">{{ __('Connections') }}</a>
                @endif
                @if ($sidebarUser)
                    <a href="{{ $withLang('dashboard.notifications.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'dashboard.notifications.') ? 'active' : '' }}">
                        <span class="nav-link-row">
                            <span>{{ __('Notifications') }}</span>
                            @if ($unreadNotifications > 0)
                                <span class="nav-badge" id="notification-badge">{{ $unreadNotifications > 99 ? '!' : $unreadNotifications }}</span>
                            @endif
                        </span>
                    </a>
                @endif
                @if ($can('admin.activities.view') || $can('admin.permissions.manage') || $can('admin.settings.manage') || $can('admin.database.manage'))
                    <p class="muted" style="margin:.7rem 0 .2rem;font-weight:700;">{{ __('Admin') }}</p>
                @endif
                @if ($can('admin.activities.view'))
                    <a href="{{ $withLang('dashboard.admin.activities.index') }}" class="nav-link {{ $currentRoute === 'dashboard.admin.activities.index' ? 'active' : '' }}">{{ __('dashboard.user_activity_nav') }}</a>
                @endif
                @if ($can('admin.permissions.manage'))
                    <a href="{{ $withLang('dashboard.admin.permissions.index') }}" class="nav-link {{ $currentRoute === 'dashboard.admin.permissions.index' ? 'active' : '' }}">{{ __('Permissions') }}</a>
                @endif
                @if ($can('admin.settings.manage'))
                    <a href="{{ $withLang('dashboard.admin.settings.index') }}" class="nav-link {{ $currentRoute === 'dashboard.admin.settings.index' ? 'active' : '' }}">{{ __('App Settings') }}</a>
                @endif
                @if ($can('admin.database.manage'))
                    <a href="{{ $withLang('dashboard.admin.database.index') }}" class="nav-link {{ $currentRoute === 'dashboard.admin.database.index' ? 'active' : '' }}">{{ __('Database Tools') }}</a>
                @endif
            </nav>
            <button type="button" class="sidebar-footer" data-modal-open="profile-modal">
                <div class="avatar">
                    @if ($sidebarAvatar)
                        <img src="{{ $sidebarAvatar }}" alt="Avatar">
                    @else
                        {{ $sidebarInitial }}
                    @endif
                </div>
                <div>
                    <div style="font-weight:800;font-size:1rem;line-height:1.1;">{{ $sidebarUser?->name }}</div>
                    <div class="muted" style="font-size:.85rem;">{{ $sidebarUser?->email }}</div>
                </div>
            </button>
        </aside>

        <main class="main">
            <div class="top page-reveal page-reveal-delay-1">
                <div>
                <h1>@yield('page_title', __('Dashboard Overview'))</h1>
                    @hasSection('page_subtitle')
                        <p class="top-subtitle">@yield('page_subtitle')</p>
                    @endif
                </div>
                <div class="top-right">
                    <label for="lang-select" style="display:none;">{{ __('Language') }}</label>
                    <select id="lang-select" class="language-select" aria-label="Language">
                        <option value="en" @selected(app()->getLocale() === 'en')>English</option>
                        <option value="id" @selected(app()->getLocale() === 'id')>Bahasa Indonesia</option>
                        <option value="zh" @selected(app()->getLocale() === 'zh')>中文</option>
                    </select>
                    <form method="POST" action="{{ route('logout', ['lang' => app()->getLocale()]) }}">
                        @csrf
                        <button type="submit" class="btn">{{ __('Log out') }}</button>
                    </form>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success page-reveal page-reveal-delay-2">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error page-reveal page-reveal-delay-2">Please check the form fields and try again.</div>
            @endif

            <div class="page-animate">
                @yield('content')
            </div>
        </main>
    </div>

    <div class="page-dim" aria-hidden="true"></div>
    <div class="toast-stack" id="toast-stack" aria-live="polite" aria-atomic="true"></div>
    <div class="modal" id="profile-modal">
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal-card">
            <div class="modal-head">
                <h2>Profile Settings</h2>
                <button class="btn-outline" type="button" data-modal-close>Close</button>
            </div>
            <div class="grid">
                <section class="card" style="grid-column: span 6;">
                    <h3 style="margin:0 0 .6rem;font-size:1rem;">Profile</h3>
                    <form method="POST" action="{{ route('dashboard.profile.update', ['lang' => app()->getLocale()]) }}" id="profile-form">
                        @csrf
                        <div class="field">
                            <label for="profile-name">Display Name</label>
                            <input id="profile-name" name="name" type="text" value="{{ $sidebarUser?->name }}" required>
                        </div>
                        <div class="field">
                            <label>Email</label>
                            <input type="text" value="{{ $sidebarUser?->email }}" readonly>
                        </div>

                        <div class="field">
                            <label>Profile Photo</label>
                            <div style="display:flex;gap:1rem;align-items:center;flex-wrap:wrap;">
                                <div id="profile-modal-avatar-preview" style="width:96px;height:96px;border-radius:16px;overflow:hidden;border:1px solid var(--line);background:#fff7d1;display:flex;align-items:center;justify-content:center;">
                                    @if ($sidebarAvatar)
                                        <img src="{{ $sidebarAvatar }}" alt="Avatar" style="width:100%;height:100%;object-fit:cover;">
                                    @else
                                        <span class="muted">No photo</span>
                                    @endif
                                </div>
                                <div>
                                    <input id="profile-modal-avatar-input" type="file" accept="image/*" data-image-editor data-output="#profile-modal-avatar-cropped" data-preview="#profile-modal-avatar-preview" data-title="Edit Profile Photo">
                                    <input type="hidden" name="avatar_cropped" id="profile-modal-avatar-cropped">
                                    <p class="muted" style="margin:.3rem 0 0;">Upload a photo, then zoom, rotate, flip, and crop it.</p>
                                </div>
                            </div>
                        </div>

                        <div class="actions" style="margin-top:.8rem;">
                            <button type="submit" class="btn">Save Profile</button>
                        </div>
                    </form>
                </section>

                <section class="card" style="grid-column: span 6;">
                    <h3 style="margin:0 0 .6rem;font-size:1rem;">Change Password</h3>
                    <form method="POST" action="{{ route('dashboard.profile.password', ['lang' => app()->getLocale()]) }}">
                        @csrf
                        <div class="field">
                            <label for="current_password">Current Password</label>
                            <input id="current_password" name="current_password" type="password" required>
                        </div>
                        <div class="field">
                            <label for="password">New Password</label>
                            <input id="password" name="password" type="password" minlength="6" required>
                        </div>
                        <div class="field">
                            <label for="password_confirmation">Confirm New Password</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" minlength="6" required>
                        </div>
                        <div class="actions">
                            <button type="submit" class="btn">Update Password</button>
                            <a class="btn-outline" href="{{ route('password.request') }}">Forgot password?</a>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>

    <div class="modal" id="image-editor-modal">
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal-card" style="width:min(980px, 100%);">
            <div class="modal-head">
                <h2 id="image-editor-title">Edit Image</h2>
                <button class="btn-outline" type="button" data-modal-close>Close</button>
            </div>
            <div class="image-editor-shell">
                <div class="image-editor-stage" id="image-editor-stage">
                    <canvas class="image-editor-canvas" id="image-editor-canvas" width="720" height="720"></canvas>
                </div>
                <div class="image-editor-sidebar">
                    <div class="image-editor-box">
                        <h3>Preview</h3>
                        <div class="image-editor-preview">
                            <canvas id="image-editor-preview-canvas" width="240" height="240"></canvas>
                        </div>
                    </div>
                    <div class="image-editor-box">
                        <h3>Zoom</h3>
                        <input id="image-editor-zoom" type="range" min="0.1" max="8" step="0.01" value="1">
                    </div>
                    <div class="image-editor-box">
                        <h3>Rotate</h3>
                        <input id="image-editor-rotate" type="range" min="-180" max="180" step="1" value="0">
                        <div class="image-editor-controls" style="margin-top:.6rem;">
                            <button type="button" class="btn-outline" id="image-editor-rotate-left">-90°</button>
                            <button type="button" class="btn-outline" id="image-editor-rotate-right">+90°</button>
                        </div>
                    </div>
                    <div class="image-editor-box">
                        <h3>Flip</h3>
                        <div class="image-editor-controls">
                            <button type="button" class="btn-outline" id="image-editor-flip-x">Flip Horizontal</button>
                            <button type="button" class="btn-outline" id="image-editor-flip-y">Flip Vertical</button>
                            <button type="button" class="btn-outline" id="image-editor-reset">Reset</button>
                        </div>
                    </div>
                    <div class="actions">
                        <button type="button" class="btn" id="image-editor-apply">Use Image</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            document.body.classList.add('page-ready');
        })();

        (function () {
            const select = document.getElementById('lang-select');
            if (!select) return;

            select.addEventListener('change', function () {
                const nextUrl = new URL(window.location.href);
                nextUrl.searchParams.set('lang', this.value);
                window.location.href = nextUrl.toString();
            });
        })();

        (function () {
            const body = document.body;

            function closeModal(modal) {
                modal.classList.remove('active');
                if (!document.querySelector('.modal.active')) {
                    body.classList.remove('modal-open');
                }
                body.style.overflow = '';
            }

            document.addEventListener('click', function (event) {
                const openTrigger = event.target.closest('[data-modal-open]');
                if (openTrigger) {
                    const modalId = openTrigger.getAttribute('data-modal-open');
                    const modal = document.getElementById(modalId);
                    if (modal) {
                        modal.classList.add('active');
                        body.classList.add('modal-open');
                        body.style.overflow = 'hidden';
                    }
                    return;
                }

                const closeTrigger = event.target.closest('[data-modal-close]');
                if (closeTrigger) {
                    const modal = closeTrigger.closest('.modal');
                    if (modal) closeModal(modal);
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key !== 'Escape') return;

                document.querySelectorAll('.modal.active').forEach(function (modal) {
                    closeModal(modal);
                });
            });
        })();

        (function () {
            const modal = document.getElementById('image-editor-modal');
            const stage = document.getElementById('image-editor-stage');
            const canvas = document.getElementById('image-editor-canvas');
            const previewCanvas = document.getElementById('image-editor-preview-canvas');
            const title = document.getElementById('image-editor-title');
            const zoomInput = document.getElementById('image-editor-zoom');
            const rotateInput = document.getElementById('image-editor-rotate');
            const applyButton = document.getElementById('image-editor-apply');
            const rotateLeft = document.getElementById('image-editor-rotate-left');
            const rotateRight = document.getElementById('image-editor-rotate-right');
            const flipXButton = document.getElementById('image-editor-flip-x');
            const flipYButton = document.getElementById('image-editor-flip-y');
            const resetButton = document.getElementById('image-editor-reset');

            if (!modal || !stage || !canvas || !previewCanvas) return;

            const ctx = canvas.getContext('2d');
            const previewCtx = previewCanvas.getContext('2d');
            const body = document.body;
            const editors = Array.from(document.querySelectorAll('[data-image-editor]'));

            const state = {
                image: null,
                output: null,
                preview: null,
                input: null,
                title: 'Edit Image',
                x: 0,
                y: 0,
                zoom: 1,
                rotation: 0,
                flipX: 1,
                flipY: 1,
                dragging: false,
                lastX: 0,
                lastY: 0,
            };

            function closeEditor() {
                modal.classList.remove('active');
                stage.classList.remove('dragging');
                state.dragging = false;
                if (!document.querySelector('.modal.active')) {
                    body.classList.remove('modal-open');
                    body.style.overflow = '';
                }
            }

            function openEditor(config) {
                state.output = document.querySelector(config.output);
                state.preview = config.preview ? document.querySelector(config.preview) : null;
                state.input = config.input;
                state.title = config.title || 'Edit Image';
                title.textContent = state.title;
                modal.classList.add('active');
                body.classList.add('modal-open');
                body.style.overflow = 'hidden';
            }

            function fitImage() {
                if (!state.image) return;
                state.zoom = 1;
                state.rotation = 0;
                state.flipX = 1;
                state.flipY = 1;
                state.x = 0;
                state.y = 0;
                zoomInput.value = '1';
                rotateInput.value = '0';
                draw();
            }

            function getBaseScale() {
                if (!state.image) return 1;
                return Math.max(canvas.width / state.image.width, canvas.height / state.image.height);
            }

            function renderToContext(targetCtx, targetCanvas) {
                if (!state.image) return;

                const baseScale = Math.max(targetCanvas.width / state.image.width, targetCanvas.height / state.image.height);
                const scale = baseScale * state.zoom;
                const offsetScaleX = targetCanvas.width / canvas.width;
                const offsetScaleY = targetCanvas.height / canvas.height;
                const centerX = targetCanvas.width / 2 + (state.x * offsetScaleX);
                const centerY = targetCanvas.height / 2 + (state.y * offsetScaleY);

                targetCtx.clearRect(0, 0, targetCanvas.width, targetCanvas.height);
                targetCtx.fillStyle = '#fff7d1';
                targetCtx.fillRect(0, 0, targetCanvas.width, targetCanvas.height);
                targetCtx.save();
                targetCtx.translate(centerX, centerY);
                targetCtx.rotate((state.rotation * Math.PI) / 180);
                targetCtx.scale(state.flipX * scale, state.flipY * scale);
                targetCtx.drawImage(state.image, -state.image.width / 2, -state.image.height / 2);
                targetCtx.restore();
            }

            function draw() {
                if (!state.image) return;
                renderToContext(ctx, canvas);
                renderToContext(previewCtx, previewCanvas);
            }

            function loadFile(file, config) {
                if (!file) return;
                const reader = new FileReader();
                reader.onload = function (event) {
                    const image = new Image();
                    image.onload = function () {
                        state.image = image;
                        openEditor(config);
                        fitImage();
                    };
                    image.src = event.target.result;
                };
                reader.readAsDataURL(file);
            }

            editors.forEach((input) => {
                input.addEventListener('change', function () {
                    const file = this.files && this.files[0];
                    if (!file) return;
                    loadFile(file, {
                        input: this,
                        output: this.dataset.output,
                        preview: this.dataset.preview,
                        title: this.dataset.title,
                    });
                });
            });

            zoomInput.addEventListener('input', function () {
                state.zoom = Math.max(0.1, parseFloat(this.value || '1'));
                draw();
            });

            rotateInput.addEventListener('input', function () {
                state.rotation = parseFloat(this.value || '0');
                draw();
            });

            rotateLeft.addEventListener('click', function () {
                state.rotation -= 90;
                rotateInput.value = String(state.rotation);
                draw();
            });

            rotateRight.addEventListener('click', function () {
                state.rotation += 90;
                rotateInput.value = String(state.rotation);
                draw();
            });

            flipXButton.addEventListener('click', function () {
                state.flipX *= -1;
                draw();
            });

            flipYButton.addEventListener('click', function () {
                state.flipY *= -1;
                draw();
            });

            resetButton.addEventListener('click', fitImage);

            stage.addEventListener('mousedown', function (event) {
                state.dragging = true;
                state.lastX = event.clientX;
                state.lastY = event.clientY;
                stage.classList.add('dragging');
            });

            window.addEventListener('mouseup', function () {
                state.dragging = false;
                stage.classList.remove('dragging');
            });

            window.addEventListener('mousemove', function (event) {
                if (!state.dragging) return;
                state.x += event.clientX - state.lastX;
                state.y += event.clientY - state.lastY;
                state.lastX = event.clientX;
                state.lastY = event.clientY;
                draw();
            });

            stage.addEventListener('wheel', function (event) {
                event.preventDefault();
                const nextZoom = state.zoom * (event.deltaY > 0 ? 0.96 : 1.04);
                state.zoom = Math.min(12, Math.max(0.1, nextZoom));
                zoomInput.value = String(state.zoom);
                draw();
            }, { passive: false });

            applyButton.addEventListener('click', function () {
                if (!state.output || !state.image) return;
                const exportCanvas = document.createElement('canvas');
                exportCanvas.width = 720;
                exportCanvas.height = 720;
                const exportCtx = exportCanvas.getContext('2d');
                renderToContext(exportCtx, exportCanvas);
                state.output.value = exportCanvas.toDataURL('image/png');

                if (state.preview) {
                    state.preview.innerHTML = '';
                    const previewImage = document.createElement('img');
                    previewImage.src = state.output.value;
                    previewImage.alt = 'Edited image preview';
                    previewImage.style.width = '100%';
                    previewImage.style.height = '100%';
                    previewImage.style.objectFit = 'cover';
                    state.preview.appendChild(previewImage);
                }

                if (state.input) {
                    state.input.value = '';
                }

                closeEditor();
            });

            modal.addEventListener('click', function (event) {
                if (!event.target.closest('.modal-card') || event.target.hasAttribute('data-modal-close')) {
                    closeEditor();
                }
            });
        })();

        (function () {
            const notificationUrl = @json($sidebarUser ? route('dashboard.notifications.poll', ['lang' => app()->getLocale()]) : null);
            const badge = document.getElementById('notification-badge');
            const toastStack = document.getElementById('toast-stack');
            if (!notificationUrl || !toastStack) return;

            let latestId = @json((int) (\App\Models\UserNotification::query()->where('user_id', $sidebarUser?->id)->max('id') ?? 0));
            let polling = false;

            function ensureBadge() {
                if (badge) return badge;
                const navRow = document.querySelector('.nav-link[href*="/dashboard/notifications"] .nav-link-row');
                if (!navRow) return null;

                const createdBadge = document.createElement('span');
                createdBadge.id = 'notification-badge';
                createdBadge.className = 'nav-badge';
                navRow.appendChild(createdBadge);
                return createdBadge;
            }

            function updateBadge(count) {
                const activeBadge = ensureBadge();
                if (!activeBadge) return;

                if (!count || count < 1) {
                    activeBadge.remove();
                    return;
                }

                activeBadge.textContent = count > 99 ? '!' : String(count);
            }

            function showToast(notification) {
                const toast = document.createElement('div');
                toast.className = 'toast';
                toast.innerHTML = `
                    <p class="toast-title">New Notification</p>
                    <p class="toast-body"><strong>${escapeHtml(notification.title || '')}</strong>${notification.body ? `<br>${escapeHtml(notification.body)}` : ''}</p>
                `;

                toastStack.appendChild(toast);
                requestAnimationFrame(() => toast.classList.add('show'));

                window.setTimeout(() => {
                    toast.classList.remove('show');
                    window.setTimeout(() => toast.remove(), 250);
                }, 5200);
            }

            function escapeHtml(value) {
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            async function pollNotifications() {
                if (polling) return;
                polling = true;

                try {
                    const url = new URL(notificationUrl, window.location.origin);
                    url.searchParams.set('after_id', String(latestId));

                    const response = await fetch(url.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });

                    if (!response.ok) return;

                    const payload = await response.json();
                    updateBadge(payload.unread_count || 0);

                    if (Array.isArray(payload.notifications) && payload.notifications.length) {
                        payload.notifications.forEach(showToast);
                    }

                    if (typeof payload.latest_id === 'number' && payload.latest_id > latestId) {
                        latestId = payload.latest_id;
                    }
                } catch (error) {
                    console.error('Notification polling failed.', error);
                } finally {
                    polling = false;
                }
            }

            window.setInterval(pollNotifications, 10000);
        })();
    </script>
</body>
</html>
