<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absences', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('student_id');
            $table->date('end_date')->nullable()->after('start_date');
        });

        DB::table('absences')->whereNotNull('absence_date')->update([
            'start_date' => DB::raw('absence_date'),
            'end_date' => DB::raw('absence_date'),
        ]);

        Schema::table('absences', function (Blueprint $table) {
            $table->dropColumn('absence_date');
        });
    }

    public function down(): void
    {
        Schema::table('absences', function (Blueprint $table) {
            $table->date('absence_date')->nullable()->after('student_id');
        });

        DB::table('absences')->update([
            'absence_date' => DB::raw('start_date'),
        ]);

        Schema::table('absences', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date']);
        });
    }
};
