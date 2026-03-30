<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        if (!$user) {
            abort(401);
        }

        if ($user->role === 'super admin') {
            return $next($request);
        }

        $roleId = DB::table('roles')->where('name', $user->role)->value('id');
        if (!$roleId) {
            abort(Response::HTTP_FORBIDDEN, __('errors.permission_required', [
                'permission' => $permission,
            ]));
        }

        $has = DB::table('role_permission')
            ->join('permissions', 'permissions.id', '=', 'role_permission.permission_id')
            ->where('role_permission.role_id', $roleId)
            ->where('permissions.name', $permission)
            ->exists();

        if (!$has) {
            abort(Response::HTTP_FORBIDDEN, __('errors.permission_required', [
                'permission' => $permission,
            ]));
        }

        return $next($request);
    }
}
