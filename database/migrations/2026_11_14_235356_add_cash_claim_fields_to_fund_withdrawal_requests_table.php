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
            $table->string('cash_claim_code', 20)->nullable()->unique()->after('cash_pickup_location');
            $table->foreignId('cash_claimed_by')->nullable()->after('processed_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('cash_claimed_outlet_id')->nullable()->after('cash_claimed_by')->constrained('pengelola_tps3r_profiles')->cascadeOnDelete();
            $table->timestamp('cash_claimed_at')->nullable()->after('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fund_withdrawal_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cash_claimed_by');
            $table->dropConstrainedForeignId('cash_claimed_outlet_id');
            $table->dropUnique(['cash_claim_code']);
            $table->dropColumn(['cash_claim_code', 'cash_claimed_at']);
        });
    }
};
