<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:users.view')->only(['index']);
        $this->middleware('permission:users.create')->only(['create', 'store']);
        $this->middleware('permission:users.update')->only(['edit', 'update', 'resetPassword']);
        $this->middleware('permission:users.delete')->only(['destroy']);
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $roleFilter = (string) $request->query('role', '');

        $query = User::query()->latest();
        if (!$this->isSuperAdmin()) {
            $query->where('role', '!=', 'super admin');
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        if ($roleFilter !== '') {
            $query->where('role', $roleFilter);
        }

        return view('dashboard.users.index', [
            'users' => $query->paginate(10)->withQueryString(),
            'roles' => $this->roles(),
            'search' => $search,
            'roleFilter' => $roleFilter,
        ]);
    }

    public function create(): View
    {
        return view('dashboard.users.create', [
            'roles' => $this->roles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $roles = $this->roles();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in($roles)],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        ActivityLogger::log(
            'user.created',
            'user',
            $user->id,
            'User created: '.$validated['email'],
            null,
            ActivityLogger::snapshot($user, 'user')
        );

        return redirect()->route('dashboard.users.index', ['lang' => app()->getLocale()])
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        $this->ensureCanManageUser($user);

        return view('dashboard.users.edit', [
            'user' => $user,
            'roles' => $this->roles(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureCanManageUser($user);

        $roles = $this->roles();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in($roles)],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        $before = ActivityLogger::snapshot($user, 'user');

        if (!empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $user->update($payload);

        if (!empty($validated['password'])) {
            ActivityLogger::log(
                'user.password_changed',
                'user',
                $user->id,
                'Password changed for: '.$user->email,
                null,
                null
            );
        }

        ActivityLogger::log(
            'user.updated',
            'user',
            $user->id,
            'User updated: '.$user->email,
            $before,
            ActivityLogger::snapshot($user, 'user')
        );

        return redirect()->route('dashboard.users.index', ['lang' => app()->getLocale()])
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->ensureCanManageUser($user);

        $before = ActivityLogger::snapshot($user, 'user');
        $user->delete();

        ActivityLogger::log(
            'user.deleted',
            'user',
            $user->id,
            'User deleted: '.$user->email,
            $before,
            null
        );

        return redirect()->route('dashboard.users.index', ['lang' => app()->getLocale()])
            ->with('success', 'User deleted successfully.');
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $this->ensureCanManageUser($user);

        $temporaryPassword = Str::random(10);
        $user->update([
            'password' => Hash::make($temporaryPassword),
        ]);

        ActivityLogger::log(
            'user.password_reset',
            'user',
            $user->id,
            'Password reset for: '.$user->email,
            null,
            null
        );

        return redirect()->route('dashboard.users.index', ['lang' => app()->getLocale()])
            ->with('success', 'Password reset for '.$user->email.'. Temporary password: '.$temporaryPassword);
    }

    /**
     * @return array<int, string>
     */
    private function roles(): array
    {
        $roles = DB::table('roles')
            ->orderBy('name')
            ->pluck('name')
            ->all();

        if (!$this->isSuperAdmin()) {
            $roles = array_values(array_filter(
                $roles,
                static fn (string $role): bool => $role !== 'super admin'
            ));
        }

        return $roles;
    }

    private function isSuperAdmin(): bool
    {
        return auth()->user()?->role === 'super admin';
    }

    private function ensureCanManageUser(User $user): void
    {
        if ($user->role === 'super admin' && !$this->isSuperAdmin()) {
            abort(403);
        }
    }

}
