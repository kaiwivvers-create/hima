<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\AppSettingVersion;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AppSettingsController extends Controller
{
    private const LOCALES = ['en', 'id', 'zh'];

    private const TEXT_KEYS = [
        'welcome_nav_dashboard',
        'welcome_nav_login',
        'welcome_nav_register',
        'welcome_hero_description',
        'welcome_section_1_title',
        'welcome_section_1_body',
        'welcome_section_2_title',
        'welcome_section_2_body',
        'welcome_section_3_title',
        'welcome_section_3_body',
        'welcome_person_title',
        'welcome_person_body',
        'welcome_map_title',
        'welcome_map_body',
    ];

    public function __construct()
    {
        $this->middleware('permission:admin.settings.manage');
    }

    public function index(): View
    {
        $current = $this->currentSettings();
        $recentBranding = collect();
        $recentContent = collect();

        if (Schema::hasTable('app_setting_versions')) {
            $baseQuery = AppSettingVersion::query()->latest();
            if (Schema::hasColumn('app_setting_versions', 'changed_branding')) {
                $recentBranding = (clone $baseQuery)->where('changed_branding', true)->take(3)->get();
                $recentContent = (clone $baseQuery)->where('changed_content', true)->take(3)->get();
            } else {
                $fallback = $baseQuery->take(3)->get();
                $recentBranding = $fallback;
                $recentContent = $fallback;
            }
        }

        return view('dashboard.admin.settings.index', [
            'appName' => $current['app_name'],
            'appLogoPath' => $current['app_logo_path'],
            'textSettings' => $current['text_overrides'],
            'recentBrandingVersions' => $recentBranding,
            'recentContentVersions' => $recentContent,
        ]);
    }

    public function updateBranding(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:120'],
            'app_logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $before = $this->currentSettings();

        $newLogoPath = $before['app_logo_path'];
        if ($request->hasFile('app_logo')) {
            $newLogoPath = $request->file('app_logo')->store('app_logo', 'public');
        }

        $changedBranding = $before['app_name'] !== $validated['app_name']
            || $before['app_logo_path'] !== $newLogoPath;
        if ($changedBranding) {
            $this->storeVersion($before, true, false);
        }

        AppSetting::setValue('app_name', $validated['app_name']);
        AppSetting::setValue('app_logo_path', $newLogoPath);

        if ($request->hasFile('app_logo') && $before['app_logo_path'] && Storage::disk('public')->exists($before['app_logo_path'])) {
            Storage::disk('public')->delete($before['app_logo_path']);
        }

        $after = $this->currentSettings();

        ActivityLogger::log(
            'app.settings.updated',
            'app_setting',
            1,
            'Updated app branding.',
            $before,
            $after
        );

        return back()->with('success', 'Branding updated.');
    }

    public function updateContent(Request $request): RedirectResponse
    {
        $before = $this->currentSettings();
        $validated = $request->validate([
            'text' => ['required', 'array'],
        ]);

        $incomingText = [];
        foreach (self::LOCALES as $locale) {
            foreach (self::TEXT_KEYS as $key) {
                $incomingText[$locale][$key] = trim((string) ($validated['text'][$locale][$key] ?? ''));
            }
        }

        $changedContent = $before['text_overrides'] !== $incomingText;
        if ($changedContent) {
            $this->storeVersion($before, false, true);
        }

        foreach (self::LOCALES as $locale) {
            foreach (self::TEXT_KEYS as $key) {
                AppSetting::setValue($this->textSettingStorageKey($key, $locale), $incomingText[$locale][$key]);
            }
        }

        ActivityLogger::log(
            'app.settings.content_updated',
            'app_setting',
            1,
            'Updated front page content settings.',
            $before,
            $this->currentSettings()
        );

        return back()->with('success', 'Front page content updated.');
    }

    public function applyBrandingVersion(int $version): RedirectResponse
    {
        if (!Schema::hasTable('app_setting_versions')) {
            return back()->withErrors(['app_name' => 'App setting versions table is missing. Run migrations first.']);
        }

        $target = AppSettingVersion::findOrFail($version);
        $before = $this->currentSettings();
        $this->storeVersion($before, true, false);

        AppSetting::setValue('app_name', $target->app_name);
        AppSetting::setValue('app_logo_path', $target->app_logo_path);

        ActivityLogger::log(
            'app.settings.branding_version_applied',
            'app_setting',
            1,
            'Applied branding iteration #'.$target->id.'.',
            $before,
            $this->currentSettings()
        );

        return back()->with('success', 'Applied branding iteration from '.$target->created_at->format('Y-m-d H:i').'.');
    }

    public function applyContentVersion(int $version): RedirectResponse
    {
        if (!Schema::hasTable('app_setting_versions')) {
            return back()->withErrors(['app_name' => 'App setting versions table is missing. Run migrations first.']);
        }

        $target = AppSettingVersion::findOrFail($version);
        $before = $this->currentSettings();
        $this->storeVersion($before, false, true);

        $versionText = Schema::hasColumn('app_setting_versions', 'text_overrides')
            ? (array) ($target->text_overrides ?? [])
            : [];
        foreach (self::LOCALES as $locale) {
            foreach (self::TEXT_KEYS as $key) {
                AppSetting::setValue(
                    $this->textSettingStorageKey($key, $locale),
                    (string) ($versionText[$locale][$key] ?? '')
                );
            }
        }

        ActivityLogger::log(
            'app.settings.content_version_applied',
            'app_setting',
            1,
            'Applied front page content iteration #'.$target->id.'.',
            $before,
            $this->currentSettings()
        );

        return back()->with('success', 'Applied content iteration from '.$target->created_at->format('Y-m-d H:i').'.');
    }

    /**
     * @return array{app_name:string, app_logo_path:?string, text_overrides:array<string,string>}
     */
    private function currentSettings(): array
    {
        $text = [];
        foreach (self::LOCALES as $locale) {
            foreach (self::TEXT_KEYS as $key) {
                $text[$locale][$key] = AppSetting::getValue($this->textSettingStorageKey($key, $locale), '');
            }
        }

        return [
            'app_name' => AppSetting::getValue('app_name', 'Student Portal') ?? 'Student Portal',
            'app_logo_path' => AppSetting::getValue('app_logo_path'),
            'text_overrides' => $text,
        ];
    }

    /**
     * @param array{app_name:string, app_logo_path:?string, text_overrides:array<string,string>} $snapshot
     */
    private function storeVersion(array $snapshot, bool $changedBranding, bool $changedContent): void
    {
        if (!Schema::hasTable('app_setting_versions')) {
            return;
        }

        $supportsFlags = Schema::hasColumn('app_setting_versions', 'changed_branding')
            && Schema::hasColumn('app_setting_versions', 'changed_content');

        AppSettingVersion::create([
            'app_name' => $snapshot['app_name'],
            'app_logo_path' => $snapshot['app_logo_path'],
            'text_overrides' => Schema::hasColumn('app_setting_versions', 'text_overrides') ? $snapshot['text_overrides'] : null,
            'changed_branding' => $supportsFlags ? $changedBranding : true,
            'changed_content' => $supportsFlags ? $changedContent : true,
            'created_by_user_id' => auth()->id(),
        ]);
    }

    private function textSettingStorageKey(string $key, string $locale): string
    {
        return $key.'.'.$locale;
    }
}
