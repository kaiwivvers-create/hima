<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

$supportedLocales = ['en', 'id', 'zh'];
$applyLocale = function (Request $request) use ($supportedLocales): void {
    $requestedLocale = $request->query('lang', session('lang', config('app.locale')));
    $locale = in_array($requestedLocale, $supportedLocales, true) ? $requestedLocale : config('app.fallback_locale');

    app()->setLocale($locale);
    session(['lang' => $locale]);
};

Route::get('/', function (Request $request) use ($applyLocale) {
    $applyLocale($request);

    return view('welcome');
});

Route::middleware('guest')->group(function () use ($applyLocale) {
    Route::get('/login', function (Request $request) use ($applyLocale) {
        $applyLocale($request);
        return view('auth.login');
    })->name('login');

    Route::post('/login', function (Request $request) use ($applyLocale) {
        $applyLocale($request);
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

    Route::get('/register', function (Request $request) use ($applyLocale) {
        $applyLocale($request);
        $captchaA = random_int(1, 9);
        $captchaB = random_int(1, 9);
        $request->session()->put('register_captcha_answer', $captchaA + $captchaB);

        return view('auth.register', [
            'captchaA' => $captchaA,
            'captchaB' => $captchaB,
        ]);
    })->name('register');

    Route::post('/register', function (Request $request) use ($applyLocale) {
        $applyLocale($request);
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

Route::middleware('auth')->group(function () use ($applyLocale) {
    Route::get('/dashboard', function (Request $request) use ($applyLocale) {
        $applyLocale($request);
        return view('dashboard');
    })->name('dashboard');

    Route::post('/logout', function (Request $request) use ($applyLocale) {
        $applyLocale($request);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    })->name('logout');
});
