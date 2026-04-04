<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('pagination.custom');
        Paginator::defaultSimpleView('pagination.simple-custom');

        view()->composer('*', function ($view): void {
            $appName = 'Student Portal';
            $appLogoPath = null;
            $locale = app()->getLocale();
            $appTextDefaults = [
                'welcome_nav_dashboard' => __('welcome.nav_dashboard'),
                'welcome_nav_login' => __('welcome.nav_login'),
                'welcome_nav_register' => __('welcome.nav_register'),
                'welcome_hero_description' => __('welcome.hero_description'),
                'welcome_section_1_title' => __('welcome.section_1_title'),
                'welcome_section_1_body' => __('welcome.section_1_body'),
                'welcome_section_2_title' => __('welcome.section_2_title'),
                'welcome_section_2_body' => __('welcome.section_2_body'),
                'welcome_section_3_title' => __('welcome.section_3_title'),
                'welcome_section_3_body' => __('welcome.section_3_body'),
                'welcome_person_title' => __('welcome.person_title'),
                'welcome_person_body' => __('welcome.person_body'),
                'welcome_map_title' => __('welcome.map_title'),
                'welcome_map_body' => __('welcome.map_body'),
                'payment_proof_rekening_text' => 'Pay to this nomor rekening:',
                'welcome_contact_email_href' => 'mailto:kaiwivvers@gmail.com',
                'welcome_contact_email_label' => 'hello@example.com',
                'welcome_contact_whatsapp_href' => 'https://wa.me/6285363410088',
                'welcome_contact_whatsapp_label' => '+62 812-3456-7890',
                'welcome_contact_instagram_href' => 'https://instagram.com/octo__pie',
                'welcome_contact_instagram_label' => '@yourusername',
            ];
            $appText = $appTextDefaults;

            try {
                if (Schema::hasTable('app_settings')) {
                    $localizedTextKeys = array_map(
                        static fn (string $key): string => $key.'.'.$locale,
                        array_keys($appTextDefaults)
                    );
                    $settings = DB::table('app_settings')
                        ->whereIn('key', array_merge(['app_name', 'app_logo_path'], array_keys($appTextDefaults), $localizedTextKeys))
                        ->pluck('value', 'key');

                    $appName = $settings['app_name'] ?? $appName;
                    $appLogoPath = $settings['app_logo_path'] ?? null;
                    foreach ($appTextDefaults as $key => $default) {
                        $value = $settings[$key.'.'.$locale] ?? ($settings[$key] ?? null);
                        $appText[$key] = is_string($value) && trim($value) !== '' ? $value : $default;
                    }
                }
            } catch (\Throwable) {
                // Avoid breaking pages when migrations are not fully applied.
            }

            $view->with('appName', $appName);
            $view->with('appLogoPath', $appLogoPath);
            $view->with('appLogoUrl', $appLogoPath ? asset('storage/'.$appLogoPath) : null);
            $view->with('appText', $appText);
        });

        Blade::if('perm', function (string $permission): bool {
            $user = Auth::user();
            if (!$user) {
                return false;
            }
            if ($user->role === 'super admin') {
                return true;
            }

            $roleId = DB::table('roles')->where('name', $user->role)->value('id');
            if (!$roleId) {
                return false;
            }

            return DB::table('role_permission')
                ->join('permissions', 'permissions.id', '=', 'role_permission.permission_id')
                ->where('role_permission.role_id', $roleId)
                ->where('permissions.name', $permission)
                ->exists();
        });
    }
}
