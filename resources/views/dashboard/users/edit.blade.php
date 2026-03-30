@extends('dashboard.layout')

@section('title', 'Edit User')
@section('page_title', 'Edit User')

@section('content')
<section class="card" style="max-width:640px;">
    <form method="POST" action="{{ route('dashboard.users.update', ['user' => $user, 'lang' => app()->getLocale()]) }}">
        @csrf
        @method('PUT')
        <div class="field">
            <label for="name">Name</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required>
        </div>
        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>
        </div>
        <div class="field">
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="">Select role</option>
                @foreach ($roles as $role)
                    <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>{{ $role }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="password">Password (leave blank to keep)</label>
            <input id="password" name="password" type="password" minlength="6">
        </div>
        <div class="actions">
            <button type="submit" class="btn">Update</button>
        </div>
    </form>
</section>
@endsection
