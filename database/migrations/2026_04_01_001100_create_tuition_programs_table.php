<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tuition_programs', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->unique();
            $table->string('name', 120);
            $table->decimal('monthly_amount', 12, 2);
            $table->timestamps();
        });

        DB::table('tuition_programs')->insert([
            [
                'slug' => 'english',
                'name' => 'English',
                'monthly_amount' => 1500000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'mandarin',
                'name' => 'Mandarin',
                'monthly_amount' => 2500000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'bimbel',
                'name' => 'Bimbel (Tutoring)',
                'monthly_amount' => 2000000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tuition_programs');
    }
};

