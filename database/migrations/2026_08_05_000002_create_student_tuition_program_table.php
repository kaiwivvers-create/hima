<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_tuition_program', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tuition_program_id')->constrained('tuition_programs')->cascadeOnDelete();
            $table->decimal('annual_amount', 12, 2)->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'tuition_program_id']);
        });

        // Migrate students who already had a single program + amount assigned.
        $students = DB::table('users')
            ->where('role', 'student')
            ->whereNotNull('tuition_program')
            ->get(['id', 'tuition_program', 'tuition_amount']);

        foreach ($students as $student) {
            $program = DB::table('tuition_programs')
                ->where('slug', $student->tuition_program)
                ->first(['id', 'monthly_amount']);
            if (!$program) {
                continue;
            }

            DB::table('student_tuition_program')->insert([
                'student_id' => $student->id,
                'tuition_program_id' => $program->id,
                'annual_amount' => $student->tuition_amount !== null
                    ? $student->tuition_amount
                    : round((float) $program->monthly_amount * 12, 2),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_tuition_program');
    }
};
