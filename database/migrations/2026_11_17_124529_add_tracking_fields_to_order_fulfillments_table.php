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
        Schema::table('order_fulfillments', function (Blueprint $table) {
            $table->string('tracking_number', 100)->nullable()->after('catatan_status');
            $table->string('courier_status', 100)->nullable()->after('tracking_number');
            $table->string('courier_status_description', 500)->nullable()->after('courier_status');
            $table->timestamp('tracking_checked_at')->nullable()->after('courier_status_description');
            $table->timestamp('next_tracking_check_at')->nullable()->index()->after('tracking_checked_at');
            $table->text('tracking_last_error')->nullable()->after('next_tracking_check_at');
            $table->timestamp('delivered_at')->nullable()->after('tracking_last_error');
            $table->timestamp('auto_complete_at')->nullable()->index()->after('delivered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_fulfillments', function (Blueprint $table) {
            $table->dropIndex(['next_tracking_check_at']);
            $table->dropIndex(['auto_complete_at']);
            $table->dropColumn([
                'tracking_number',
                'courier_status',
                'courier_status_description',
                'tracking_checked_at',
                'next_tracking_check_at',
                'tracking_last_error',
                'delivered_at',
                'auto_complete_at',
            ]);
        });
    }
};
