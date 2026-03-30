<?php

use App\Http\Controllers\Dashboard\AbsenceController;
use App\Http\Controllers\Dashboard\AttendanceController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\PaymentController;
use App\Http\Controllers\Dashboard\StudentController;
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
        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/dashboard');
    })->name('register.post');
});

Route::middleware(['auth', 'apply.locale'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('/dashboard')->name('dashboard.')->group(function () {
        Route::post('attendances/mark', [AttendanceController::class, 'mark'])->name('attendances.mark');
        Route::resource('students', StudentController::class)->except(['show']);
        Route::resource('attendances', AttendanceController::class)->except(['show']);
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
