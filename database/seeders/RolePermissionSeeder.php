<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = DB::table('roles')->pluck('id', 'name');
        $permissions = DB::table('permissions')->pluck('id', 'name');

        $now = now();

        $defaults = [
            'super admin' => array_keys($permissions->all()),
            'admin' => [
                'dashboard.view',
                'attendance.view',
                'attendance.mark',
                'attendance.create',
                'attendance.update',
                'attendance.delete',
                'payments.view',
                'payments.create',
                'payments.update',
                'payments.delete',
                'absences.view',
                'absences.create',
                'absences.update',
                'absences.delete',
                'students.view',
                'students.create',
                'students.update',
                'students.delete',
                'profile.view',
                'profile.update',
                'profile.password',
            ],
            'teacher' => [
                'dashboard.view',
                'attendance.view',
                'attendance.mark',
                'students.view',
                'absences.view',
                'absences.create',
                'profile.view',
                'profile.update',
                'profile.password',
            ],
            'parent' => [
                'dashboard.view',
                'payments.view',
                'payments.update',
                'absences.view',
                'profile.view',
                'profile.update',
                'profile.password',
            ],
            'student' => [
                'dashboard.view',
                'attendance.view',
                'absences.view',
                'absences.create',
                'payments.view',
                'profile.view',
                'profile.update',
                'profile.password',
            ],
        ];

        foreach ($defaults as $roleName => $permissionNames) {
            $roleId = $roles[$roleName] ?? null;
            if (!$roleId) {
                continue;
            }

            DB::table('role_permission')->where('role_id', $roleId)->delete();

            $rows = [];
            foreach ($permissionNames as $permissionName) {
                $permissionId = $permissions[$permissionName] ?? null;
                if (!$permissionId) {
                    continue;
                }
                $rows[] = [
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($rows)) {
                DB::table('role_permission')->insert($rows);
            }
        }
    }
}
