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
        Schema::create('marketplace_settlement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fund_withdrawal_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_fulfillment_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->unique(['fund_withdrawal_request_id', 'order_fulfillment_id'], 'marketplace_settlement_request_fulfillment_unique');
            $table->index(['order_fulfillment_id', 'released_at'], 'marketplace_settlement_active_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_settlement_items');
    }
};
