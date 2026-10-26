<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('retribution_bills', function (Blueprint $table) {
            $table->unique(
                ['warga_id', 'periode_bulan', 'periode_tahun'],
                'retribution_bills_warga_period_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retribution_bills', function (Blueprint $table) {
            $table->dropUnique('retribution_bills_warga_period_unique');
        });
    }
};
