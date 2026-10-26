<?php

namespace Database\Factories;

use App\Enums\OrderFulfillmentStatus;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OrderFulfillment> */
class OrderFulfillmentFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'seller_id' => User::factory()->pengelolaTps3r(),
            'status' => OrderFulfillmentStatus::Menunggu,
            'catatan_status' => null,
            'processed_at' => null,
            'ready_at' => null,
            'completed_at' => null,
            'cancelled_at' => null,
        ];
    }
}
