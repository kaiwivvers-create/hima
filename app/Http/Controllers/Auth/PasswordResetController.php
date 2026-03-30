<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function requestForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendReset(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            return back()->withErrors(['email' => 'We could not find that email.']);
        }

        $token = Str::random(64);
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);

        Mail::raw(
            "Reset your password using this link:\n\n".$resetUrl."\n\nThis link expires in 60 minutes.",
            fn ($message) => $message->to($user->email)->subject('Password Reset')
        );

        return back()->with('success', 'Password reset email sent.');
    }

    public function resetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $validated['email'])
            ->first();

        if (!$record || !Hash::check($validated['token'], $record->token)) {
            return back()->withErrors(['email' => 'Invalid or expired reset token.']);
        }

        $createdAt = Carbon::parse($record->created_at);
        if ($createdAt->diffInMinutes(now()) > 60) {
            return back()->withErrors(['email' => 'Reset token expired.']);
        }

        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

        return redirect()->route('login')->with('success', 'Password updated. Please log in.');
    }
}
