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
                    <div id="profile-page-avatar-preview" style="width:96px;height:96px;border-radius:16px;overflow:hidden;border:1px solid var(--line);background:#fff7d1;display:flex;align-items:center;justify-content:center;">
                        @if ($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="Avatar" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            <span class="muted">No photo</span>
                        @endif
                    </div>
                    <div>
                        <input id="profile-page-avatar-input" type="file" accept="image/*" data-image-editor data-output="#profile-page-avatar-cropped" data-preview="#profile-page-avatar-preview" data-title="Edit Profile Photo">
                        <input type="hidden" name="avatar_cropped" id="profile-page-avatar-cropped">
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

@endsection
