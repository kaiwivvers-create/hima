@extends('dashboard.layout')

@section('title', 'Create User')
@section('page_title', 'Create User')

@section('content')
<section class="card" style="max-width:640px;">
    <form method="POST" action="{{ route('dashboard.users.store', ['lang' => app()->getLocale()]) }}">
        @csrf
        <div class="field">
            <label for="name">Name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required>
        </div>
        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required>
        </div>
        <div class="field">
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="">Select role</option>
                @foreach ($roles as $role)
                    <option value="{{ $role }}" @selected(old('role') === $role)>{{ $role }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" minlength="6" required>
        </div>
        <div class="actions">
            <button type="submit" class="btn">Save</button>
        </div>
    </form>
</section>
@endsection
