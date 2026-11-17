<?php

namespace Database\Factories;

use App\Models\FundWithdrawalRequest;
use App\Models\MarketplaceSettlementItem;
use App\Models\OrderFulfillment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketplaceSettlementItem>
 */
class MarketplaceSettlementItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fund_withdrawal_request_id' => FundWithdrawalRequest::factory(),
            'order_fulfillment_id' => OrderFulfillment::factory(),
            'amount' => 25000,
            'released_at' => null,
        ];
    }
}
