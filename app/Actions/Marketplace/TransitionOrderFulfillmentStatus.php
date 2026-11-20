<?php

namespace App\Actions\Marketplace;

use App\Enums\OrderFulfillmentStatus;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\OrderItem;
use App\Models\PointLedger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransitionOrderFulfillmentStatus
{
    public function __construct(
        public SendMarketplaceTransactionNotifications $transactionNotifications,
    ) {}

    /**
     * @throws ValidationException
     */
    public function handle(
        OrderFulfillment $fulfillment,
        OrderFulfillmentStatus $targetStatus,
        ?string $note = null,
        ?string $trackingNumber = null,
    ): OrderFulfillment {
        $result = DB::transaction(function () use ($fulfillment, $targetStatus, $note, $trackingNumber): array {
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

            $trackingAttributes = [];
            if ($targetStatus === OrderFulfillmentStatus::Dikirim && $order->shipping_source === 'rajaongkir') {
                $normalizedTrackingNumber = mb_strtoupper(trim(
                    $trackingNumber ?? $lockedFulfillment->tracking_number ?? '',
                ));

                if ($normalizedTrackingNumber === '') {
                    throw ValidationException::withMessages([
                        'tracking_number' => ['Nomor resi wajib diisi untuk pengiriman kurir luar kota.'],
                    ]);
                }

                $trackingAttributes = [
                    'tracking_number' => $normalizedTrackingNumber,
                    'next_tracking_check_at' => now(),
                    'tracking_last_error' => null,
                ];

                if ($lockedFulfillment->tracking_number !== $normalizedTrackingNumber) {
                    $trackingAttributes = [
                        ...$trackingAttributes,
                        'courier_status' => null,
                        'courier_status_description' => null,
                        'tracking_checked_at' => null,
                        'delivered_at' => null,
                        'auto_complete_at' => null,
                    ];
                }
            }

            // Callback status yang sama bersifat idempoten tanpa mengulang efek samping stok/poin
            if ($lockedFulfillment->status === $targetStatus) {
                if ($trackingAttributes !== []) {
                    $lockedFulfillment->update($trackingAttributes);
                }

                return [
                    'fulfillment' => $lockedFulfillment->fresh(),
                    'previous_status' => $lockedFulfillment->status,
                    'changed' => false,
                ];
            }

            if (
                $targetStatus !== OrderFulfillmentStatus::Dibatalkan
                && $order->metode_pembayaran === 'midtrans'
                && $order->status === 'pending'
            ) {
                throw ValidationException::withMessages([
                    'status' => ['Pesanan Midtrans belum dibayar oleh pembeli sehingga belum dapat diproses.'],
                ]);
            }

            $previousStatus = $lockedFulfillment->status;
            $attributes = [
                'status' => $targetStatus,
                'catatan_status' => $note,
                ...$trackingAttributes,
            ];

            if ($targetStatus->timestampColumn() !== null) {
                $attributes[$targetStatus->timestampColumn()] = now();
            }

            $lockedFulfillment->update($attributes);

            if (
                $targetStatus === OrderFulfillmentStatus::Dibatalkan
                && $previousStatus !== OrderFulfillmentStatus::Dibatalkan
                && $order->status !== 'dibatalkan'
            ) {
                if ($order->metode_pembayaran === 'midtrans' && $order->status !== 'pending') {
                    throw ValidationException::withMessages([
                        'status' => ['Pesanan Midtrans yang telah dibayar memerlukan proses pengembalian dana oleh admin.'],
                    ]);
                }

                // Restore stock for items belonging to this seller
                $orderItems = OrderItem::where('order_id', $order->id)
                    ->whereHas('product', fn ($q) => $q->where('seller_id', $lockedFulfillment->seller_id))
                    ->with('product')
                    ->get();

                foreach ($orderItems as $item) {
                    $item->product->increment('stok', $item->jumlah);
                }

                // If paid with points, refund points atomically via PointLedger once
                if ($order->metode_pembayaran === 'poin_sampah') {
                    $pointsToRefund = (int) ceil((float) $orderItems->sum('subtotal'));
                    if ($pointsToRefund > 0) {
                        PointLedger::record(
                            $order->buyer_id,
                            'kredit',
                            'refund_marketplace',
                            $pointsToRefund,
                            "Pengembalian poin pembatalan pesanan {$order->nomor_pesanan}",
                            $order
                        );
                    }
                }
            }

            /** @var Collection<int, OrderFulfillment> $allFulfillments */
            $allFulfillments = OrderFulfillment::query()
                ->where('order_id', $order->id)
                ->lockForUpdate()
                ->get();

            $this->syncOrderStatus($order, $allFulfillments);

            return [
                'fulfillment' => $lockedFulfillment->fresh(),
                'previous_status' => $previousStatus,
                'changed' => true,
            ];
        });

        /** @var OrderFulfillment $updatedFulfillment */
        $updatedFulfillment = $result['fulfillment'];
        if ($result['changed']) {
            $this->transactionNotifications->fulfillmentStatusChanged(
                $updatedFulfillment,
                $result['previous_status'],
            );
        }

        return $updatedFulfillment;
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
        $activeFulfillments = $fulfillments->reject(
            fn (OrderFulfillment $fulfillment): bool => $fulfillment->status === OrderFulfillmentStatus::Dibatalkan,
        );
        $allCompleted = $activeFulfillments->isNotEmpty() && $activeFulfillments->every(
            fn (OrderFulfillment $fulfillment): bool => $fulfillment->status === OrderFulfillmentStatus::Selesai,
        );
        $allShipped = $activeFulfillments->isNotEmpty() && $activeFulfillments->every(
            fn (OrderFulfillment $fulfillment): bool => in_array($fulfillment->status, [
                OrderFulfillmentStatus::Dikirim,
                OrderFulfillmentStatus::Selesai,
            ], true),
        );
        $allReady = $activeFulfillments->isNotEmpty() && $activeFulfillments->every(
            fn (OrderFulfillment $fulfillment): bool => in_array($fulfillment->status, [
                OrderFulfillmentStatus::SiapDikirim,
                OrderFulfillmentStatus::Dikirim,
                OrderFulfillmentStatus::Selesai,
            ], true),
        );
        $hasProgress = $activeFulfillments->contains(
            fn (OrderFulfillment $fulfillment): bool => $fulfillment->status !== OrderFulfillmentStatus::Menunggu,
        );

        $status = match (true) {
            $allCancelled => 'dibatalkan',
            $allCompleted => 'selesai',
            $allShipped => 'dikirim',
            $allReady => 'siap_dikirim',
            $hasProgress => 'diproses',
            default => $order->status,
        };

        if ($order->status !== $status) {
            $order->update(['status' => $status]);
        }
    }
}
