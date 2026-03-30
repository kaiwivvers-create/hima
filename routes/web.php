<?php

use App\Http\Controllers\Dashboard\AbsenceController;
use App\Http\Controllers\Dashboard\ActivityController;
use App\Http\Controllers\Dashboard\AttendanceController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\ParentConnectionController;
use App\Http\Controllers\Dashboard\PaymentController;
use App\Http\Controllers\Dashboard\ProfileController;
use App\Http\Controllers\Dashboard\PermissionController;
use App\Http\Controllers\Dashboard\ConnectionsController;
use App\Http\Controllers\Dashboard\StudentController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->middleware('apply.locale');

Route::middleware(['guest', 'apply.locale'])->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendReset'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');

    Route::post('/login', function (Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        return back()
            ->withErrors(['email' => 'Invalid email or password.'])
            ->onlyInput('email');
    })->name('login.post');

    Route::get('/register', function (Request $request) {
        $captchaA = random_int(1, 9);
        $captchaB = random_int(1, 9);
        $request->session()->put('register_captcha_answer', $captchaA + $captchaB);

        return view('auth.register', [
            'captchaA' => $captchaA,
            'captchaB' => $captchaB,
        ]);
    })->name('register');

    Route::post('/register', function (Request $request) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:student,parent'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'captcha_answer' => ['required', 'integer'],
        ]);

        $expectedCaptcha = (int) $request->session()->get('register_captcha_answer', -1);
        if ((int) $validated['captcha_answer'] !== $expectedCaptcha) {
            return back()
                ->withErrors(['captcha_answer' => __('auth.captcha_invalid')])
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        $request->session()->forget('register_captcha_answer');
        \App\Services\ActivityLogger::log(
            'user.registered',
            'user',
            $user->id,
            'New user registered: '.$user->email,
            null,
            \App\Services\ActivityLogger::snapshot($user, 'user')
        );
        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/dashboard');
    })->name('register.post');
});

Route::middleware(['auth', 'apply.locale'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('/dashboard')->name('dashboard.')->group(function () {
        Route::post('parent-connection/request', [ParentConnectionController::class, 'request'])->name('parent-connection.request');
        Route::post('parent-connection/{requestId}/accept', [ParentConnectionController::class, 'accept'])->name('parent-connection.accept');
        Route::post('parent-connection/{requestId}/reject', [ParentConnectionController::class, 'reject'])->name('parent-connection.reject');
        Route::get('connections', [ConnectionsController::class, 'index'])->name('connections.index');

        Route::post('attendances/mark', [AttendanceController::class, 'mark'])->name('attendances.mark');
        Route::resource('attendances', AttendanceController::class)->except(['show']);

        Route::resource('students', StudentController::class)->except(['show']);

        Route::resource('users', UserController::class)->except(['show']);

        Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
        Route::post('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

        Route::get('admin/activities', [ActivityController::class, 'index'])
            ->middleware('permission:admin.activities.view')
            ->name('admin.activities.index');
        Route::post('admin/activities/{activity}/revert', [ActivityController::class, 'revert'])
            ->middleware('permission:admin.activities.view')
            ->name('admin.activities.revert');
        Route::delete('admin/activities/{activity}/purge', [ActivityController::class, 'purge'])
            ->middleware('permission:admin.activities.view')
            ->name('admin.activities.purge');
        Route::post('admin/activities/versions/{version}/revert', [ActivityController::class, 'revertVersion'])
            ->middleware('permission:admin.activities.view')
            ->name('admin.activities.versions.revert');

        Route::get('admin/permissions', [PermissionController::class, 'index'])
            ->middleware('permission:admin.permissions.manage')
            ->name('admin.permissions.index');
        Route::post('admin/permissions', [PermissionController::class, 'update'])
            ->middleware('permission:admin.permissions.manage')
            ->name('admin.permissions.update');

        Route::post('payments/plan', [PaymentController::class, 'generatePlan'])->name('payments.plan');
        Route::post('payments/{payment}/pay', [PaymentController::class, 'pay'])->name('payments.pay');
        Route::get('payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
        Route::resource('payments', PaymentController::class)->except(['show']);

        Route::resource('absences', AbsenceController::class)->except(['show']);
    });

    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    })->name('logout');
});
