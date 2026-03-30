@extends('dashboard.layout')

@section('title', 'Profile Settings')
@section('page_title', 'Profile Settings')

@section('content')
@php
    $avatarUrl = $user->avatar_path ? asset('storage/'.$user->avatar_path) : null;
@endphp

<div class="grid">
    <section class="card" style="grid-column: span 6;">
        <h2 style="margin:0 0 .6rem;font-size:1.1rem;">Profile</h2>
        <form method="POST" action="{{ route('dashboard.profile.update', ['lang' => app()->getLocale()]) }}" id="profile-form">
            @csrf
            <div class="field">
                <label for="name">Display Name</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="field">
                <label>Email</label>
                <input type="text" value="{{ $user->email }}" readonly>
            </div>

            <div class="field">
                <label>Profile Photo</label>
                <div style="display:flex;gap:1rem;align-items:center;flex-wrap:wrap;">
                    <div style="width:96px;height:96px;border-radius:16px;overflow:hidden;border:1px solid var(--line);background:#fff7d1;display:flex;align-items:center;justify-content:center;">
                        @if ($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="Avatar" style="width:100%;height:100%;object-fit:cover;">
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
        <h2 style="margin:0 0 .6rem;font-size:1.1rem;">Change Password</h2>
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
            </div>
        </form>
    </section>
</div>

<script>
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
@endsection
