<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $roles = [
            ['name' => 'teacher', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'admin', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'super admin', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'student', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'parent', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('roles')->upsert($roles, ['name'], ['updated_at']);
    }
}
