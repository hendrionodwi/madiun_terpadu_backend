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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('snap_token')->nullable()->after('catatan');
            $table->text('snap_redirect_url')->nullable()->after('snap_token');
            $table->json('payment_gateway_response')->nullable()->after('snap_redirect_url');
            $table->timestamp('paid_at')->nullable()->after('payment_gateway_response');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'snap_token',
                'snap_redirect_url',
                'payment_gateway_response',
                'paid_at',
            ]);
        });
    }
};
