<?php

namespace Database\Seeders;

use App\Enums\OrderFulfillmentStatus;
use App\Models\Order;
use App\Models\OrderFulfillment;
use Illuminate\Database\Seeder;

class OrderFulfillmentSeeder extends Seeder
{
    public function run(): void
    {
        Order::query()
            ->with('items.product:id,seller_id')
            ->each(function (Order $order): void {
                $sellerIds = $order->items
                    ->pluck('product.seller_id')
                    ->filter()
                    ->unique();

                foreach ($sellerIds as $sellerId) {
                    OrderFulfillment::firstOrCreate(
                        ['order_id' => $order->id, 'seller_id' => $sellerId],
                        ['status' => OrderFulfillmentStatus::Menunggu],
                    );
                }
            });
    }
}
