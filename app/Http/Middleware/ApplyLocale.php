<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class ApplyLocale
{
    /** @var array<string, array<string, string>> */
    private static array $replacementCache = [];

    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = ['en', 'id', 'zh', 'in'];
        $requestedLocale = strtolower((string) $request->query('lang', session('lang', config('app.locale'))));

        $normalizedLocale = $requestedLocale === 'in' ? 'id' : $requestedLocale;
        $fallback = (string) config('app.fallback_locale', 'en');
        $locale = in_array($normalizedLocale, $supportedLocales, true)
            ? $normalizedLocale
            : $fallback;

        app()->setLocale($locale);
        session(['lang' => $locale]);

        $response = $next($request);

        if ($locale === 'en') {
            return $response;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');
        if (!str_contains($contentType, 'text/html')) {
            return $response;
        }

        $content = $response->getContent();
        if (!is_string($content) || $content === '') {
            return $response;
        }

        $replacementMap = $this->buildReplacementMap($locale);
        if ($replacementMap === []) {
            return $response;
        }

        $response->setContent($this->replaceOutsideScriptAndStyle($content, $replacementMap));

        return $response;
    }

    /**
     * @return array<string, string>
     */
    private function buildReplacementMap(string $locale): array
    {
        if (isset(self::$replacementCache[$locale])) {
            return self::$replacementCache[$locale];
        }

        $map = [];
        $localeJsonPath = lang_path($locale.'.json');
        if (File::exists($localeJsonPath)) {
            $json = json_decode((string) File::get($localeJsonPath), true);
            if (is_array($json)) {
                foreach ($json as $english => $translated) {
                    if (is_string($english) && is_string($translated) && $english !== '' && $translated !== '') {
                        $map[$english] = $translated;
                    }
                }
            }
        }

        $enFiles = File::files(lang_path('en'));
        foreach ($enFiles as $enFile) {
            $fileName = $enFile->getFilename();
            $group = pathinfo($fileName, PATHINFO_FILENAME);
            $localeFilePath = lang_path($locale.DIRECTORY_SEPARATOR.$fileName);
            if (!File::exists($localeFilePath)) {
                continue;
            }

            /** @var mixed $enGroup */
            $enGroup = trans($group, [], 'en');
            /** @var mixed $localeGroup */
            $localeGroup = trans($group, [], $locale);

            if (!is_array($enGroup) || !is_array($localeGroup)) {
                continue;
            }

            $this->appendArrayReplacements($map, $enGroup, $localeGroup);
        }

        uksort($map, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));
        self::$replacementCache[$locale] = $map;

        return $map;
    }

    /**
     * @param array<string, string> $map
     * @param array<mixed> $enArray
     * @param array<mixed> $localeArray
     */
    private function appendArrayReplacements(array &$map, array $enArray, array $localeArray): void
    {
        foreach ($enArray as $key => $enValue) {
            if (!array_key_exists($key, $localeArray)) {
                continue;
            }

            $localeValue = $localeArray[$key];
            if (is_array($enValue) && is_array($localeValue)) {
                $this->appendArrayReplacements($map, $enValue, $localeValue);
                continue;
            }

            if (is_string($enValue) && is_string($localeValue) && $enValue !== '' && $localeValue !== '') {
                $map[$enValue] = $localeValue;
            }
        }
    }

    /**
     * @param array<string, string> $replacementMap
     */
    private function replaceOutsideScriptAndStyle(string $content, array $replacementMap): string
    {
        if ($content === '' || $replacementMap === []) {
            return $content;
        }

        $parts = preg_split(
            '/(<script\b[^>]*>.*?<\/script>|<style\b[^>]*>.*?<\/style>)/is',
            $content,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        );

        if (!is_array($parts)) {
            return strtr($content, $replacementMap);
        }

        foreach ($parts as $index => $part) {
            if ($part === '') {
                continue;
            }

            if (preg_match('/^<script\b/i', $part) === 1 || preg_match('/^<style\b/i', $part) === 1) {
                continue;
            }

            $parts[$index] = strtr($part, $replacementMap);
        }

        return implode('', $parts);
    }
}
