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
        Schema::create('order_fulfillments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('menunggu');
            $table->text('catatan_status')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'seller_id']);
            $table->index(['seller_id', 'status']);
        });

        $existingFulfillments = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('waste_products', 'waste_products.id', '=', 'order_items.product_id')
            ->select(['order_items.order_id', 'waste_products.seller_id', 'orders.status'])
            ->distinct()
            ->get();

        if ($existingFulfillments->isNotEmpty()) {
            $timestamp = now();
            DB::table('order_fulfillments')->insert(
                $existingFulfillments->map(fn ($fulfillment): array => [
                    'order_id' => $fulfillment->order_id,
                    'seller_id' => $fulfillment->seller_id,
                    'status' => match ($fulfillment->status) {
                        'diproses' => 'diproses',
                        'selesai' => 'selesai',
                        'dibatalkan' => 'dibatalkan',
                        default => 'menunggu',
                    },
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ])->all(),
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_fulfillments');
    }
};
