<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fund_withdrawal_requests', function (Blueprint $table) {
            $table->foreignId('reward_fund_allocation_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('point_conversion_rate', 14, 4)->nullable()->after('points_used');
            $table->string('destination_provider', 50)->nullable()->after('destination_type');
            $table->string('destination_account_name', 100)->nullable()->after('destination_account');
            $table->string('cash_pickup_location')->nullable()->after('destination_account_name');
            $table->string('payout_reference', 100)->nullable()->after('admin_note')->index();
            $table->string('payout_proof_path')->nullable()->after('payout_reference');
            $table->text('payout_note')->nullable()->after('payout_proof_path');
            $table->text('failure_note')->nullable()->after('payout_note');
            $table->foreignId('processed_by')->nullable()->after('reviewed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('processed_at')->nullable()->after('reviewed_at');
            $table->timestamp('paid_at')->nullable()->after('processed_at');
            $table->timestamp('failed_at')->nullable()->after('paid_at');
        });

        DB::table('master_data')
            ->whereIn('group', ['point_conversion', 'point_conversions'])
            ->where(function ($query) {
                $query->where('numeric_value', '<', 0.1)
                    ->orWhere('numeric_value', '>', 10);
            })
            ->update([
                'numeric_value' => 1,
                'unit' => 'rupiah/poin',
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fund_withdrawal_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reward_fund_allocation_id');
            $table->dropConstrainedForeignId('processed_by');
            $table->dropIndex(['payout_reference']);
            $table->dropColumn([
                'point_conversion_rate',
                'destination_provider',
                'destination_account_name',
                'cash_pickup_location',
                'payout_reference',
                'payout_proof_path',
                'payout_note',
                'failure_note',
                'processed_at',
                'paid_at',
                'failed_at',
            ]);
        });
    }
};
