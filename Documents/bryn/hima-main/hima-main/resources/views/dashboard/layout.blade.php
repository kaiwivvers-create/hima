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
            z-index: 60;
            padding: 1rem;
        }

        .modal.active { display: flex; }

        .modal-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(20, 14, 0, .65);
            opacity: 0;
            transition: opacity .2s ease;
        }

        .modal-card {
            position: relative;
            z-index: 1;
            width: min(760px, 100%);
            max-height: 92vh;
            overflow: auto;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 1rem;
            box-shadow: 0 14px 35px rgba(0, 0, 0, .2);
            opacity: 0;
            transform: translateY(16px);
            transition: opacity .2s ease, transform .2s ease;
        }

        .modal.active .modal-backdrop {
            opacity: 1;
        }

        .modal.active .modal-card {
            opacity: 1;
            transform: translateY(0);
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
                                <span class="nav-badge">{{ $unreadNotifications > 99 ? '!' : $unreadNotifications }}</span>
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
            <div class="top">
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
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error">Please check the form fields and try again.</div>
            @endif

            @yield('content')
        </main>
    </div>

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
                                <div style="width:96px;height:96px;border-radius:16px;overflow:hidden;border:1px solid var(--line);background:#fff7d1;display:flex;align-items:center;justify-content:center;">
                                    @if ($sidebarAvatar)
                                        <img src="{{ $sidebarAvatar }}" alt="Avatar" style="width:100%;height:100%;object-fit:cover;">
                                    @else
                                        <span class="muted">No photo</span>
                                    @endif
                                </div>
                                <div>
                                    <input id="avatar-input" type="file" accept="image/*">
                                    <input type="hidden" name="avatar_cropped" id="avatar-cropped">
                                    <p class="muted" style="margin:.3rem 0 0;">Upload a square photo. You can crop it below.</p>
                                </div>
                            </div>
                        </div>

                        <div id="cropper" class="card" style="display:none;margin-top:.6rem;">
                            <div style="display:flex;gap:1rem;flex-wrap:wrap;align-items:center;">
                                <canvas id="crop-canvas" width="240" height="240" style="border:1px solid var(--line);border-radius:12px;background:#fff7d1;"></canvas>
                                <div>
                                    <label for="zoom" class="muted" style="display:block;margin-bottom:.3rem;">Zoom</label>
                                    <input id="zoom" type="range" min="1" max="3" step="0.01" value="1">
                                    <div class="actions" style="margin-top:.6rem;">
                                        <button type="button" class="btn-outline" id="use-crop">Use Crop</button>
                                    </div>
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

        (function () {
            const body = document.body;

            function closeModal(modal) {
                modal.classList.remove('active');
                body.style.overflow = '';
            }

            document.addEventListener('click', function (event) {
                const openTrigger = event.target.closest('[data-modal-open]');
                if (openTrigger) {
                    const modalId = openTrigger.getAttribute('data-modal-open');
                    const modal = document.getElementById(modalId);
                    if (modal) {
                        modal.classList.add('active');
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
            const input = document.getElementById('avatar-input');
            const cropper = document.getElementById('cropper');
            const canvas = document.getElementById('crop-canvas');
            const zoom = document.getElementById('zoom');
            const useCrop = document.getElementById('use-crop');
            const output = document.getElementById('avatar-cropped');

            if (!input || !canvas) return;

            const ctx = canvas.getContext('2d');
            let image = null;
            let state = { x: 0, y: 0, scale: 1, dragging: false, lastX: 0, lastY: 0 };

            function draw() {
                if (!image) return;
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                const baseScale = Math.max(canvas.width / image.width, canvas.height / image.height);
                const totalScale = baseScale * state.scale;
                const drawW = image.width * totalScale;
                const drawH = image.height * totalScale;
                const dx = (canvas.width - drawW) / 2 + state.x;
                const dy = (canvas.height - drawH) / 2 + state.y;

                ctx.save();
                ctx.fillStyle = '#fff7d1';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(image, dx, dy, drawW, drawH);
                ctx.restore();
            }

            function clamp() {
                if (!image) return;
                const baseScale = Math.max(canvas.width / image.width, canvas.height / image.height);
                const totalScale = baseScale * state.scale;
                const drawW = image.width * totalScale;
                const drawH = image.height * totalScale;

                const minX = (canvas.width - drawW);
                const minY = (canvas.height - drawH);
                state.x = Math.min(Math.max(state.x, minX / 2), -minX / 2);
                state.y = Math.min(Math.max(state.y, minY / 2), -minY / 2);
            }

            function updateCrop() {
                output.value = canvas.toDataURL('image/png');
            }

            input.addEventListener('change', function () {
                const file = this.files && this.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function (e) {
                    image = new Image();
                    image.onload = function () {
                        state = { x: 0, y: 0, scale: 1, dragging: false, lastX: 0, lastY: 0 };
                        zoom.value = '1';
                        cropper.style.display = 'block';
                        draw();
                        updateCrop();
                    };
                    image.src = e.target.result;
                };
                reader.readAsDataURL(file);
            });

            zoom.addEventListener('input', function () {
                state.scale = parseFloat(this.value);
                clamp();
                draw();
                updateCrop();
            });

            canvas.addEventListener('mousedown', function (e) {
                state.dragging = true;
                state.lastX = e.offsetX;
                state.lastY = e.offsetY;
            });

            window.addEventListener('mouseup', function () {
                state.dragging = false;
            });

            canvas.addEventListener('mousemove', function (e) {
                if (!state.dragging) return;
                const dx = e.offsetX - state.lastX;
                const dy = e.offsetY - state.lastY;
                state.x += dx;
                state.y += dy;
                state.lastX = e.offsetX;
                state.lastY = e.offsetY;
                clamp();
                draw();
                updateCrop();
            });

            if (useCrop) {
                useCrop.addEventListener('click', function () {
                    updateCrop();
                });
            }
        })();
    </script>
</body>
</html>
