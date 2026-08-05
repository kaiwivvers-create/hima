<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tuition_programs', function (Blueprint $table) {
            $table->decimal('bi_monthly_amount', 12, 2)->nullable()->after('monthly_amount');
            $table->decimal('triannual_amount', 12, 2)->nullable()->after('bi_monthly_amount');
            $table->decimal('quarterly_amount', 12, 2)->nullable()->after('triannual_amount');
            $table->decimal('yearly_amount', 12, 2)->nullable()->after('quarterly_amount');
        });

        // Backfill sensible per-plan prices for existing programs.
        // The "english" program bills 4x a year (quarterly) instead of 3x a year.
        $programs = DB::table('tuition_programs')->get(['slug', 'monthly_amount']);
        foreach ($programs as $program) {
            $monthly = (float) $program->monthly_amount;
            $isQuarterly = $program->slug === 'english';

            DB::table('tuition_programs')
                ->where('slug', $program->slug)
                ->update([
                    'bi_monthly_amount' => round($monthly * 2, 2),
                    'triannual_amount' => $isQuarterly ? null : round($monthly * 3, 2),
                    'quarterly_amount' => $isQuarterly ? round($monthly * 3, 2) : null,
                    'yearly_amount' => round($monthly * 12, 2),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('tuition_programs', function (Blueprint $table) {
            $table->dropColumn(['yearly_amount', 'quarterly_amount', 'triannual_amount', 'bi_monthly_amount']);
        });
    }
};
