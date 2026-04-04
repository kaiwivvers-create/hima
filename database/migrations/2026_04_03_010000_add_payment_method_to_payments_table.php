<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint ) {
            ->enum('payment_method', ['cash', 'transfer'])->default('transfer')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint ) {
            ->dropColumn('payment_method');
        });
    }
};
