<?php

use App\Http\Controllers\Dashboard\AbsenceController;
use App\Http\Controllers\Dashboard\ActivityController;
use App\Http\Controllers\Dashboard\AppSettingsController;
use App\Http\Controllers\Dashboard\AttendanceController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\ParentConnectionController;
use App\Http\Controllers\Dashboard\PaymentController;
use App\Http\Controllers\Dashboard\NotificationController;
use App\Http\Controllers\Dashboard\ProfileController;
use App\Http\Controllers\Dashboard\PermissionController;
use App\Http\Controllers\Dashboard\ConnectionsController;
use App\Http\Controllers\Dashboard\DatabaseToolsController;
use App\Http\Controllers\Dashboard\StudentController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
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
            'role' => ['required', 'in:parent'],
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
            'role' => 'parent',
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
        $user->sendEmailVerificationNotification();
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice', ['lang' => app()->getLocale()])
            ->with('success', __('auth.verify_email_sent'));
    })->name('register.post');
});

Route::middleware(['auth', 'apply.locale'])->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('dashboard', ['lang' => app()->getLocale()])
            ->with('success', __('auth.verify_email_done'));
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', __('auth.verify_email_resent'));
    })->middleware('throttle:6,1')->name('verification.send');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/notifications', [NotificationController::class, 'index'])->name('dashboard.notifications.index');
    Route::post('/dashboard/notifications/read-all', [NotificationController::class, 'readAll'])->name('dashboard.notifications.read-all');
    Route::post('/dashboard/notifications/{notification}/read', [NotificationController::class, 'read'])->name('dashboard.notifications.read');
    Route::post('/dashboard/notifications/{notification}/archive', [NotificationController::class, 'archive'])->name('dashboard.notifications.archive');
    Route::post('/dashboard/notifications/{notification}/unarchive', [NotificationController::class, 'unarchive'])->name('dashboard.notifications.unarchive');
    Route::delete('/dashboard/notifications/{notification}', [NotificationController::class, 'destroy'])->name('dashboard.notifications.destroy');

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
        Route::get('admin/settings', [AppSettingsController::class, 'index'])
            ->middleware('permission:admin.settings.manage')
            ->name('admin.settings.index');
        Route::post('admin/settings/branding', [AppSettingsController::class, 'updateBranding'])
            ->middleware('permission:admin.settings.manage')
            ->name('admin.settings.branding.update');
        Route::post('admin/settings/content', [AppSettingsController::class, 'updateContent'])
            ->middleware('permission:admin.settings.manage')
            ->name('admin.settings.content.update');
        Route::post('admin/settings/versions/{version}/apply-branding', [AppSettingsController::class, 'applyBrandingVersion'])
            ->middleware('permission:admin.settings.manage')
            ->whereNumber('version')
            ->name('admin.settings.versions.apply-branding');
        Route::post('admin/settings/versions/{version}/apply-content', [AppSettingsController::class, 'applyContentVersion'])
            ->middleware('permission:admin.settings.manage')
            ->whereNumber('version')
            ->name('admin.settings.versions.apply-content');
        Route::get('admin/database', [DatabaseToolsController::class, 'index'])
            ->middleware('permission:admin.database.manage')
            ->name('admin.database.index');
        Route::post('admin/database/backup', [DatabaseToolsController::class, 'backup'])
            ->middleware('permission:admin.database.manage')
            ->name('admin.database.backup');
        Route::get('admin/database/download/{file}', [DatabaseToolsController::class, 'download'])
            ->middleware('permission:admin.database.manage')
            ->name('admin.database.download');
        Route::post('admin/database/import', [DatabaseToolsController::class, 'import'])
            ->middleware('permission:admin.database.manage')
            ->name('admin.database.import');
        Route::post('admin/database/restore/{file}', [DatabaseToolsController::class, 'restore'])
            ->middleware('permission:admin.database.manage')
            ->name('admin.database.restore');
        Route::post('admin/database/reset', [DatabaseToolsController::class, 'reset'])
            ->middleware('permission:admin.database.manage')
            ->name('admin.database.reset');

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
