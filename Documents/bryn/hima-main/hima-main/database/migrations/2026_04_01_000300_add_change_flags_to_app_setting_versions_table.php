<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('app_setting_versions')) {
            return;
        }

        Schema::table('app_setting_versions', function (Blueprint $table) {
            if (!Schema::hasColumn('app_setting_versions', 'changed_branding')) {
                $table->boolean('changed_branding')->default(true)->after('text_overrides');
            }
            if (!Schema::hasColumn('app_setting_versions', 'changed_content')) {
                $table->boolean('changed_content')->default(true)->after('changed_branding');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('app_setting_versions')) {
            return;
        }

        Schema::table('app_setting_versions', function (Blueprint $table) {
            if (Schema::hasColumn('app_setting_versions', 'changed_content')) {
                $table->dropColumn('changed_content');
            }
            if (Schema::hasColumn('app_setting_versions', 'changed_branding')) {
                $table->dropColumn('changed_branding');
            }
        });
    }
};
