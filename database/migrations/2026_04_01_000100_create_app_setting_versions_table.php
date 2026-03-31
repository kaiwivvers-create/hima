<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_setting_versions', function (Blueprint $table) {
            $table->id();
            $table->string('app_name', 120);
            $table->string('app_logo_path')->nullable();
            $table->json('text_overrides')->nullable();
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->timestamps();
            $table->index('created_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_setting_versions');
    }
};
