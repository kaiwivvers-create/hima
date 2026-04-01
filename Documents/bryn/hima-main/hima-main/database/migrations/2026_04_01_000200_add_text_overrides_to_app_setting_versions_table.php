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

        if (!Schema::hasColumn('app_setting_versions', 'text_overrides')) {
            Schema::table('app_setting_versions', function (Blueprint $table) {
                $table->json('text_overrides')->nullable()->after('app_logo_path');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('app_setting_versions')) {
            return;
        }

        if (Schema::hasColumn('app_setting_versions', 'text_overrides')) {
            Schema::table('app_setting_versions', function (Blueprint $table) {
                $table->dropColumn('text_overrides');
            });
        }
    }
};
