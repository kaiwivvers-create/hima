@extends('dashboard.layout')

@section('title', 'Permissions')
@section('page_title', 'Permissions')

@section('content')
@php
    $groups = [
        'Dashboard' => ['dashboard.view'],
        'Attendance' => ['attendance.view', 'attendance.mark', 'attendance.create', 'attendance.update', 'attendance.delete'],
        'Payments' => ['payments.view', 'payments.create', 'payments.update', 'payments.delete'],
        'Absences' => ['absences.view', 'absences.create', 'absences.update', 'absences.delete'],
        'Students' => ['students.view', 'students.create', 'students.update', 'students.delete'],
        'Users' => ['users.view', 'users.create', 'users.update', 'users.delete'],
        'Admin' => ['admin.activities.view', 'admin.permissions.manage'],
        'Profile' => ['profile.view', 'profile.update', 'profile.password'],
    ];
    $permissionMap = $permissions->keyBy('name');
@endphp

<form method="POST" action="{{ route('dashboard.admin.permissions.update', ['lang' => app()->getLocale()]) }}">
    @csrf

    @foreach ($groups as $groupName => $groupPermissions)
        <section class="card" style="margin-bottom:.9rem; background: #fff9df;">
            <h3 style="margin:0 0 .6rem;font-size:1rem; color:#3b2d00;">{{ $groupName }}</h3>
            <div style="overflow:auto;">
                <table class="table" style="background:#fffdf1;border-radius:10px;overflow:hidden;table-layout:fixed;">
                    <thead>
                        <tr>
                            <th style="min-width:140px;background:#fff4c8;text-align:left;">Role</th>
                            @foreach ($groupPermissions as $permissionName)
                                @php
                                    $permission = $permissionMap->get($permissionName);
                                @endphp
                                <th style="min-width:150px;background:#fff4c8;text-align:center;">
                                    <div style="font-weight:700;">{{ $permission?->label ?? $permissionName }}</div>
                                    <div class="muted" style="font-size:.75rem;">{{ $permissionName }}</div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $role)
                            <tr>
                                <td style="font-weight:700; text-transform: capitalize;">{{ $role->name }}</td>
                                @foreach ($groupPermissions as $permissionName)
                                    @php
                                        $permission = $permissionMap->get($permissionName);
                                        $permissionId = $permission?->id;
                                        $isChecked = $permissionId ? in_array($permissionId, $assigned[$role->id] ?? [], true) : false;
                                    @endphp
                                    <td style="padding: .4rem 0; text-align:center;">
                                        @if ($permissionId)
                                            <div style="display:flex; justify-content:center; align-items:center; width:100%;">
                                                <input type="checkbox" name="role_permissions[{{ $role->id }}][{{ $permissionId }}]" @checked($isChecked) style="margin:0;">
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endforeach

    <div class="actions" style="margin-top:.9rem;">
        <button type="submit" class="btn">Save Permissions</button>
    </div>
</form>
@endsection
