<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:profile.view')->only(['index']);
        $this->middleware('permission:profile.update')->only(['update']);
        $this->middleware('permission:profile.password')->only(['updatePassword']);
    }

    public function index(): View
    {
        return view('dashboard.profile.index', [
            'user' => auth()->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'avatar_cropped' => ['nullable', 'string'],
        ]);

        $user = auth()->user();
        $before = ActivityLogger::snapshot($user, 'user');
        $user->name = $validated['name'];

        if (!empty($validated['avatar_cropped'])) {
            $path = $this->storeAvatar($validated['avatar_cropped'], $user->id);
            if ($path) {
                $user->avatar_path = $path;
            }
        }

        $user->save();

        ActivityLogger::log(
            'user.updated',
            'user',
            $user->id,
            'Profile updated: '.$user->email,
            $before,
            ActivityLogger::snapshot($user, 'user')
        );

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = auth()->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return back()->with('success', 'Password updated.');
    }

    private function storeAvatar(string $dataUrl, int $userId): ?string
    {
        if (!str_starts_with($dataUrl, 'data:image/')) {
            return null;
        }

        [$meta, $content] = explode(',', $dataUrl, 2);
        if (!$content) {
            return null;
        }

        $extension = 'png';
        if (str_contains($meta, 'image/jpeg')) {
            $extension = 'jpg';
        } elseif (str_contains($meta, 'image/webp')) {
            $extension = 'webp';
        }

        $binary = base64_decode($content, true);
        if ($binary === false) {
            return null;
        }

        $path = 'avatars/user-'.$userId.'-'.time().'.'.$extension;
        Storage::disk('public')->put($path, $binary);

        return $path;
    }
}
