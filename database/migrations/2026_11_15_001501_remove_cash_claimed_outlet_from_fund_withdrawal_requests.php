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
        Schema::table('fund_withdrawal_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cash_claimed_outlet_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fund_withdrawal_requests', function (Blueprint $table) {
            $table->foreignId('cash_claimed_outlet_id')
                ->nullable()
                ->after('cash_claimed_by')
                ->constrained('pengelola_tps3r_profiles')
                ->cascadeOnDelete();
        });
    }
};
