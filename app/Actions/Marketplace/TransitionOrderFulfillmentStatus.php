<?php

namespace App\Actions\Marketplace;

use App\Enums\OrderFulfillmentStatus;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\OrderItem;
use App\Models\WargaProfile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransitionOrderFulfillmentStatus
{
    /**
     * @throws ValidationException
     */
    public function handle(
        OrderFulfillment $fulfillment,
        OrderFulfillmentStatus $targetStatus,
        ?string $note = null,
    ): OrderFulfillment {
        return DB::transaction(function () use ($fulfillment, $targetStatus, $note): OrderFulfillment {
            $order = Order::query()
                ->whereKey($fulfillment->order_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedFulfillment = OrderFulfillment::query()
                ->whereKey($fulfillment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedFulfillment->status->canTransitionTo($targetStatus)) {
                throw ValidationException::withMessages([
                    'status' => ["Status {$lockedFulfillment->status->label()} tidak dapat langsung diubah menjadi {$targetStatus->label()}."],
                ]);
            }

            $attributes = [
                'status' => $targetStatus,
                'catatan_status' => $note,
            ];

            if ($lockedFulfillment->status !== $targetStatus && $targetStatus->timestampColumn() !== null) {
                $attributes[$targetStatus->timestampColumn()] = now();
            }

            $lockedFulfillment->update($attributes);

            if ($targetStatus === OrderFulfillmentStatus::Dibatalkan) {
                // Restore stock for items belonging to this seller
                $orderItems = OrderItem::where('order_id', $order->id)
                    ->whereHas('product', fn ($q) => $q->where('seller_id', $lockedFulfillment->seller_id))
                    ->with('product')
                    ->get();

                foreach ($orderItems as $item) {
                    if ($item->product) {
                        $item->product->increment('stok', $item->jumlah);
                    }
                }

                // If paid with points, refund points for these items
                if ($order->metode_pembayaran === 'poin_sampah') {
                    $subtotalRefund = (float) $orderItems->sum('subtotal');
                    $buyerProfile = WargaProfile::where('user_id', $order->buyer_id)->first();
                    if ($buyerProfile && $subtotalRefund > 0) {
                        $buyerProfile->increment('saldo_poin', (int) ceil($subtotalRefund));
                    }
                }
            }

            /** @var Collection<int, OrderFulfillment> $allFulfillments */
            $allFulfillments = OrderFulfillment::query()
                ->where('order_id', $order->id)
                ->lockForUpdate()
                ->get();

            $this->syncOrderStatus($order, $allFulfillments);

            return $lockedFulfillment->fresh();
        });
    }

    /** @param Collection<int, OrderFulfillment> $fulfillments */
    private function syncOrderStatus(Order $order, Collection $fulfillments): void
    {
        if ($fulfillments->isEmpty()) {
            return;
        }

        $allCancelled = $fulfillments->every(
            fn (OrderFulfillment $fulfillment): bool => $fulfillment->status === OrderFulfillmentStatus::Dibatalkan,
        );
        $allTerminal = $fulfillments->every(
            fn (OrderFulfillment $fulfillment): bool => $fulfillment->status->isTerminal(),
        );
        $hasProgress = $fulfillments->contains(
            fn (OrderFulfillment $fulfillment): bool => in_array($fulfillment->status, [
                OrderFulfillmentStatus::Diproses,
                OrderFulfillmentStatus::SiapDikirim,
                OrderFulfillmentStatus::Selesai,
            ], true),
        );

        $status = match (true) {
            $allCancelled => 'dibatalkan',
            $allTerminal => 'selesai',
            $hasProgress => 'diproses',
            default => $order->status,
        };

        if ($order->status !== $status) {
            $order->update(['status' => $status]);
        }
    }
}
