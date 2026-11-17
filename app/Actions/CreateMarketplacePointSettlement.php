<?php

namespace App\Actions;

use App\Enums\FundWithdrawalStatus;
use App\Enums\OrderFulfillmentStatus;
use App\Models\FundWithdrawalRequest;
use App\Models\MarketplaceSettlementItem;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateMarketplacePointSettlement
{
    public const MINIMUM_SETTLEMENT_AMOUNT = 10000;

    public function __construct(private CreateFundWithdrawalRequest $fundWithdrawalRequest) {}

    /** @return array{fulfillments: Collection<int, OrderFulfillment>, amount: int} */
    public function eligibleSummary(User $seller): array
    {
        $fulfillments = $this->candidateQuery($seller)
            ->with([
                'order.items.product',
                'marketplaceSettlementItems' => fn ($query) => $query->whereNull('released_at'),
            ])
            ->oldest('completed_at')
            ->get();

        $eligibleFulfillments = $fulfillments
            ->filter(fn (OrderFulfillment $fulfillment): bool => $this->fulfillmentAvailableAmount($fulfillment, $seller) > 0)
            ->values();

        return [
            'fulfillments' => $eligibleFulfillments,
            'amount' => $this->calculateAvailableAmount($eligibleFulfillments, $seller),
        ];
    }

    /** @param array<string, mixed> $data */
    public function handle(User $seller, array $data): FundWithdrawalRequest
    {
        if (! $seller->isPengelolaTps3r()) {
            throw ValidationException::withMessages([
                'source' => ['Settlement marketplace poin hanya tersedia untuk pengelola TPS3R.'],
            ]);
        }

        return DB::transaction(function () use ($seller, $data): FundWithdrawalRequest {
            User::query()->whereKey($seller->id)->lockForUpdate()->firstOrFail();

            $fulfillments = $this->candidateQuery($seller)
                ->with([
                    'order.items.product',
                    'marketplaceSettlementItems' => fn ($query) => $query
                        ->whereNull('released_at')
                        ->lockForUpdate(),
                ])
                ->oldest('completed_at')
                ->lockForUpdate()
                ->get();
            $availableAmount = $this->calculateAvailableAmount($fulfillments, $seller);
            $requestedAmount = (int) $data['amount'];

            if ($availableAmount < self::MINIMUM_SETTLEMENT_AMOUNT) {
                throw ValidationException::withMessages([
                    'settlement' => ['Belum ada transaksi poin selesai dengan total minimal Rp10.000 yang dapat diselesaikan.'],
                ]);
            }

            if ($requestedAmount < self::MINIMUM_SETTLEMENT_AMOUNT) {
                throw ValidationException::withMessages([
                    'amount' => ['Nominal settlement minimal Rp10.000.'],
                ]);
            }

            if ($requestedAmount > $availableAmount) {
                throw ValidationException::withMessages([
                    'amount' => ['Nominal melebihi saldo settlement yang tersedia.'],
                ]);
            }

            if ($this->fundWithdrawalRequest->availableRewardBudget() < $requestedAmount) {
                throw ValidationException::withMessages([
                    'settlement' => ['Alokasi kas reward Admin DLH belum mencukupi untuk settlement ini.'],
                ]);
            }

            $withdrawal = FundWithdrawalRequest::create([
                'user_id' => $seller->id,
                'source' => 'marketplace_points',
                'amount' => $requestedAmount,
                'destination_type' => $data['destination_type'],
                'destination_provider' => $data['destination_provider'],
                'destination_account' => $data['destination_account'],
                'destination_account_name' => $data['destination_account_name'],
                'status' => FundWithdrawalStatus::Pending->value,
                'requester_note' => $data['requester_note'] ?? null,
            ]);

            $remainingAmount = $requestedAmount;

            foreach ($fulfillments as $fulfillment) {
                $availableFulfillmentAmount = $this->fulfillmentAvailableAmount($fulfillment, $seller);
                $allocatedAmount = min($availableFulfillmentAmount, $remainingAmount);

                if ($allocatedAmount <= 0) {
                    continue;
                }

                MarketplaceSettlementItem::create([
                    'fund_withdrawal_request_id' => $withdrawal->id,
                    'order_fulfillment_id' => $fulfillment->id,
                    'amount' => $allocatedAmount,
                ]);

                $remainingAmount -= $allocatedAmount;

                if ($remainingAmount === 0) {
                    break;
                }
            }

            if ($remainingAmount !== 0) {
                throw ValidationException::withMessages([
                    'amount' => ['Nominal settlement tidak dapat dialokasikan ke transaksi yang tersedia.'],
                ]);
            }

            return $withdrawal->load('marketplaceSettlementItems.fulfillment.order');
        });
    }

    /** @return Builder<OrderFulfillment> */
    private function candidateQuery(User $seller): Builder
    {
        return OrderFulfillment::query()
            ->where('seller_id', $seller->id)
            ->where('status', OrderFulfillmentStatus::Selesai->value)
            ->whereHas('order', fn (Builder $query): Builder => $query
                ->where('metode_pembayaran', 'poin_sampah')
                ->whereIn('status', Order::REVENUE_STATUSES));
    }

    /** @param Collection<int, OrderFulfillment> $fulfillments */
    private function calculateAvailableAmount(Collection $fulfillments, User $seller): int
    {
        return (int) $fulfillments->sum(
            fn (OrderFulfillment $fulfillment): int => $this->fulfillmentAvailableAmount($fulfillment, $seller)
        );
    }

    private function fulfillmentAvailableAmount(OrderFulfillment $fulfillment, User $seller): int
    {
        $allocatedAmount = (int) round((float) $fulfillment->marketplaceSettlementItems->sum('amount'));

        return max(0, $this->fulfillmentGrossAmount($fulfillment, $seller) - $allocatedAmount);
    }

    private function fulfillmentGrossAmount(OrderFulfillment $fulfillment, User $seller): int
    {
        return (int) round((float) $fulfillment->order->items
            ->filter(fn ($item): bool => $item->product->seller_id === $seller->id)
            ->sum('subtotal'));
    }
}
