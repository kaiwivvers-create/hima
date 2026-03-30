<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('pagination.custom');
        Paginator::defaultSimpleView('pagination.simple-custom');

        Blade::if('perm', function (string $permission): bool {
            $user = Auth::user();
            if (!$user) {
                return false;
            }
            if ($user->role === 'super admin') {
                return true;
            }

            $roleId = DB::table('roles')->where('name', $user->role)->value('id');
            if (!$roleId) {
                return false;
            }

            return DB::table('role_permission')
                ->join('permissions', 'permissions.id', '=', 'role_permission.permission_id')
                ->where('role_permission.role_id', $roleId)
                ->where('permissions.name', $permission)
                ->exists();
        });
    }
}
