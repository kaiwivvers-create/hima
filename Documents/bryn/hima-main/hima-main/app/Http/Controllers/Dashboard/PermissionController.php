<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:admin.permissions.manage');
    }

    public function index(): View
    {
        $roles = DB::table('roles')->orderBy('name')->get();
        $permissions = DB::table('permissions')->orderBy('name')->get();
        $pivot = DB::table('role_permission')->get()->groupBy('role_id');

        $assigned = [];
        foreach ($roles as $role) {
            $rolePermissions = $pivot->get($role->id) ?? collect();
            $assigned[$role->id] = $rolePermissions->pluck('permission_id')->all();
        }

        return view('dashboard.admin.permissions.index', [
            'roles' => $roles,
            'permissions' => $permissions,
            'assigned' => $assigned,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $payload = $request->input('role_permissions', []);
        $roles = DB::table('roles')->pluck('id')->all();
        $permissions = DB::table('permissions')->pluck('id')->all();

        DB::transaction(function () use ($payload, $roles, $permissions) {
            foreach ($roles as $roleId) {
                $selected = array_keys($payload[$roleId] ?? []);
                $selectedIds = array_values(array_intersect($permissions, array_map('intval', $selected)));

                DB::table('role_permission')->where('role_id', $roleId)->delete();

                $rows = [];
                foreach ($selectedIds as $permissionId) {
                    $rows[] = [
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                if (!empty($rows)) {
                    DB::table('role_permission')->insert($rows);
                }
            }
        });

        return back()->with('success', 'Permissions updated.');
    }
}
