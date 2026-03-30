<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = ['en', 'id', 'zh'];
        $requestedLocale = $request->query('lang', session('lang', config('app.locale')));

        $locale = in_array($requestedLocale, $supportedLocales, true)
            ? $requestedLocale
            : config('app.fallback_locale');

        app()->setLocale($locale);
        session(['lang' => $locale]);

        return $next($request);
    }
}
