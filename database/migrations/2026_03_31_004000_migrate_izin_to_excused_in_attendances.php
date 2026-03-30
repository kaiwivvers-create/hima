<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('attendances')
            ->where('status', 'izin')
            ->update(['status' => 'excused']);
    }

    public function down(): void
    {
        DB::table('attendances')
            ->where('status', 'excused')
            ->update(['status' => 'izin']);
    }
};
