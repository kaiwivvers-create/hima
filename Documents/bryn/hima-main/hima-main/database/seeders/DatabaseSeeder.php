<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call(RolesSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(RolePermissionSeeder::class);

        $defaultPassword = Hash::make('250510');

        User::firstOrCreate(
            ['email' => 'kai@example.com'],
            [
                'name' => 'Kai',
                'password' => $defaultPassword,
                'role' => 'super admin',
            ]
        );

        $students = [
            ['name' => 'Ava', 'email' => 'ava@example.com'],
            ['name' => 'Liam', 'email' => 'liam@example.com'],
            ['name' => 'Mia', 'email' => 'mia@example.com'],
            ['name' => 'Noah', 'email' => 'noah@example.com'],
            ['name' => 'Emma', 'email' => 'emma@example.com'],
            ['name' => 'Wei', 'email' => 'wei@example.com'],
            ['name' => 'Ling', 'email' => 'ling@example.com'],
            ['name' => 'Hao', 'email' => 'hao@example.com'],
            ['name' => 'Mei', 'email' => 'mei@example.com'],
            ['name' => 'Tao', 'email' => 'tao@example.com'],
        ];

        $teachers = [
            ['name' => 'Aria', 'email' => 'aria@example.com'],
            ['name' => 'Mason', 'email' => 'mason@example.com'],
            ['name' => 'Isla', 'email' => 'isla@example.com'],
            ['name' => 'Henry', 'email' => 'henry@example.com'],
            ['name' => 'Nora', 'email' => 'nora@example.com'],
            ['name' => 'Li', 'email' => 'li@example.com'],
            ['name' => 'Chen', 'email' => 'chen@example.com'],
            ['name' => 'Xia', 'email' => 'xia@example.com'],
            ['name' => 'Yue', 'email' => 'yue@example.com'],
            ['name' => 'Jun', 'email' => 'jun@example.com'],
        ];

        $parents = [
            ['name' => 'Olivia', 'email' => 'olivia@example.com'],
            ['name' => 'Lucas', 'email' => 'lucas@example.com'],
            ['name' => 'Zoe', 'email' => 'zoe@example.com'],
            ['name' => 'Ethan', 'email' => 'ethan@example.com'],
            ['name' => 'Luna', 'email' => 'luna@example.com'],
            ['name' => 'Fang', 'email' => 'fang@example.com'],
            ['name' => 'Hui', 'email' => 'hui@example.com'],
            ['name' => 'Qiang', 'email' => 'qiang@example.com'],
            ['name' => 'Yan', 'email' => 'yan@example.com'],
            ['name' => 'Lei', 'email' => 'lei@example.com'],
        ];

        $this->seedRoleUsers($students, 'student', $defaultPassword);
        $this->seedRoleUsers($teachers, 'teacher', $defaultPassword);
        $this->seedRoleUsers($parents, 'parent', $defaultPassword);
    }

    /**
     * @param array<int, array{name:string,email:string}> $rows
     */
    private function seedRoleUsers(array $rows, string $role, string $password): void
    {
        foreach ($rows as $row) {
            $name = $row['name'];
            $email = $row['email'];
            User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => $password,
                    'role' => $role,
                ]
            );
        }
    }
}
