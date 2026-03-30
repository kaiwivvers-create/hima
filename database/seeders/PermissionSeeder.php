<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $permissions = [
            ['name' => 'dashboard.view', 'label' => 'View Dashboard'],
            ['name' => 'attendance.view', 'label' => 'View Attendance'],
            ['name' => 'attendance.mark', 'label' => 'Mark Attendance'],
            ['name' => 'attendance.create', 'label' => 'Create Attendance'],
            ['name' => 'attendance.update', 'label' => 'Update Attendance'],
            ['name' => 'attendance.delete', 'label' => 'Delete Attendance'],
            ['name' => 'payments.view', 'label' => 'View Payments'],
            ['name' => 'payments.create', 'label' => 'Create Payments'],
            ['name' => 'payments.update', 'label' => 'Update Payments'],
            ['name' => 'payments.delete', 'label' => 'Delete Payments'],
            ['name' => 'absences.view', 'label' => 'View Absences'],
            ['name' => 'absences.create', 'label' => 'Create Absences'],
            ['name' => 'absences.update', 'label' => 'Update Absences'],
            ['name' => 'absences.delete', 'label' => 'Delete Absences'],
            ['name' => 'students.view', 'label' => 'View Students'],
            ['name' => 'students.create', 'label' => 'Create Students'],
            ['name' => 'students.update', 'label' => 'Update Students'],
            ['name' => 'students.delete', 'label' => 'Delete Students'],
            ['name' => 'users.view', 'label' => 'View Users'],
            ['name' => 'users.create', 'label' => 'Create Users'],
            ['name' => 'users.update', 'label' => 'Update Users'],
            ['name' => 'users.delete', 'label' => 'Delete Users'],
            ['name' => 'admin.activities.view', 'label' => 'View User Activity'],
            ['name' => 'admin.permissions.manage', 'label' => 'Manage Permissions'],
            ['name' => 'profile.view', 'label' => 'View Profile'],
            ['name' => 'profile.update', 'label' => 'Update Profile'],
            ['name' => 'profile.password', 'label' => 'Change Password'],
        ];

        DB::table('permissions')->upsert(
            array_map(fn ($p) => array_merge($p, ['created_at' => $now, 'updated_at' => $now]), $permissions),
            ['name'],
            ['label', 'updated_at']
        );
    }
}
