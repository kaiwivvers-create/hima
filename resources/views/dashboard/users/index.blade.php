@extends('dashboard.layout')

@section('title', 'Users')
@section('page_title', 'Users')

@section('content')
<div class="page-actions" style="justify-content:space-between; align-items:flex-end; flex-wrap:wrap;">
    <form method="GET" action="{{ route('dashboard.users.index', ['lang' => app()->getLocale()]) }}" class="actions" style="align-items:end; justify-content:flex-start;">
        <div class="field" style="margin:0; min-width:220px;">
            <label for="search">Search</label>
            <input id="search" name="search" type="text" value="{{ $search ?? '' }}" placeholder="Name or email">
        </div>
        <div class="field" style="margin:0; min-width:180px;">
            <label for="role">Role</label>
            <select id="role" name="role">
                <option value="">All</option>
                @foreach ($roles as $role)
                    <option value="{{ $role }}" @selected(($roleFilter ?? '') === $role)>{{ $role }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-outline" style="padding:.52rem .8rem;">Filter</button>
    </form>
    @perm('users.create')
        <button class="btn" type="button" data-modal-open="user-create-modal" style="padding:.52rem .8rem;">Add User</button>
    @endperm
</div>

<section class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->role }}</td>
                    <td>
                        <div class="actions">
                            @perm('users.update')
                                <button class="btn-outline" type="button" data-modal-open="user-edit-{{ $user->id }}">Edit</button>
                            @endperm
                            @perm('users.delete')
                                <button class="btn btn-danger" type="button" data-modal-open="user-delete-{{ $user->id }}">Delete</button>
                            @endperm
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="muted">No users found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">{{ $users->links() }}</div>
</section>

@perm('users.create')
<div class="modal" id="user-create-modal">
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-card">
        <div class="modal-head">
            <h2>Add User</h2>
            <button class="btn-outline" type="button" data-modal-close>Close</button>
        </div>
        <form method="POST" action="{{ route('dashboard.users.store', ['lang' => app()->getLocale()]) }}">
            @csrf
            <div class="field">
                <label for="create-name">Name</label>
                <input id="create-name" name="name" type="text" required>
            </div>
            <div class="field">
                <label for="create-email">Email</label>
                <input id="create-email" name="email" type="email" required>
            </div>
            <div class="field">
                <label for="create-role">Role</label>
                <select id="create-role" name="role" required>
                    <option value="">Select role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}">{{ $role }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="create-password">Password</label>
                <input id="create-password" name="password" type="password" minlength="6" required>
            </div>
            <div class="actions">
                <button type="submit" class="btn">Save</button>
            </div>
        </form>
    </div>
</div>
@endperm

@foreach ($users as $user)
    @perm('users.update')
    <div class="modal" id="user-edit-{{ $user->id }}">
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal-card">
            <div class="modal-head">
                <h2>Edit User</h2>
                <button class="btn-outline" type="button" data-modal-close>Close</button>
            </div>
            <form method="POST" action="{{ route('dashboard.users.update', ['user' => $user, 'lang' => app()->getLocale()]) }}">
                @csrf
                @method('PUT')
                <div class="field">
                    <label for="edit-name-{{ $user->id }}">Name</label>
                    <input id="edit-name-{{ $user->id }}" name="name" type="text" value="{{ $user->name }}" required>
                </div>
                <div class="field">
                    <label for="edit-email-{{ $user->id }}">Email</label>
                    <input id="edit-email-{{ $user->id }}" name="email" type="email" value="{{ $user->email }}" required>
                </div>
                <div class="field">
                    <label for="edit-role-{{ $user->id }}">Role</label>
                    <select id="edit-role-{{ $user->id }}" name="role" required>
                        <option value="">Select role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" @selected($user->role === $role)>{{ $role }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="edit-password-{{ $user->id }}">Password (leave blank to keep)</label>
                    <input id="edit-password-{{ $user->id }}" name="password" type="password" minlength="6">
                </div>
                <div class="actions">
                    <button type="submit" class="btn">Update</button>
                </div>
            </form>
        </div>
    </div>
    @endperm

    @perm('users.delete')
    <div class="modal" id="user-delete-{{ $user->id }}">
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal-card">
            <div class="modal-head">
                <h2>Delete User</h2>
                <button class="btn-outline" type="button" data-modal-close>Close</button>
            </div>
            <p>Delete <strong>{{ $user->name }}</strong>? This cannot be undone.</p>
            <form method="POST" action="{{ route('dashboard.users.destroy', ['user' => $user, 'lang' => app()->getLocale()]) }}">
                @csrf
                @method('DELETE')
                <div class="actions">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
    @endperm
@endforeach
@endsection
