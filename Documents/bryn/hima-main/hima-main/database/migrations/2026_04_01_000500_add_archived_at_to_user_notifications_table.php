<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('user_notifications', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('read_at');
                $table->index(['user_id', 'archived_at']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_notifications', function (Blueprint $table) {
            if (Schema::hasColumn('user_notifications', 'archived_at')) {
                $table->dropColumn('archived_at');
            }
        });
    }
};
