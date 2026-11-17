<?php

namespace App\Actions\Marketplace;

use App\Enums\OrderFulfillmentStatus;
use App\Models\Order;
use App\Models\PointLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelMarketplaceOrder
{
    public function handle(Order $order, string $reason): Order
    {
        return DB::transaction(function () use ($order, $reason): Order {
            $lockedOrder = Order::query()
                ->with(['items.product', 'fulfillments'])
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($lockedOrder->status, ['siap_dikirim', 'dikirim', 'selesai', 'dibatalkan'], true)) {
                throw ValidationException::withMessages([
                    'order' => 'Pesanan tidak dapat dibatalkan karena sudah dikirim, selesai, atau telah dibatalkan.',
                ]);
            }

            if ($lockedOrder->metode_pembayaran === 'midtrans' && $lockedOrder->status !== 'pending') {
                throw ValidationException::withMessages([
                    'order' => 'Pesanan Midtrans yang telah dibayar memerlukan proses pengembalian dana oleh admin.',
                ]);
            }

            foreach ($lockedOrder->fulfillments as $fulfillment) {
                if ($fulfillment->status !== OrderFulfillmentStatus::Dibatalkan) {
                    $fulfillment->update([
                        'status' => OrderFulfillmentStatus::Dibatalkan,
                        'catatan_status' => $reason,
                        'cancelled_at' => now(),
                    ]);
                }
            }

            foreach ($lockedOrder->items as $item) {
                $item->product->increment('stok', $item->jumlah);
            }

            if ($lockedOrder->metode_pembayaran === 'poin_sampah') {
                PointLedger::record(
                    $lockedOrder->buyer_id,
                    'kredit',
                    'refund_marketplace',
                    (int) ceil($lockedOrder->total_harga),
                    "Pengembalian poin pembatalan pesanan {$lockedOrder->nomor_pesanan}",
                    $lockedOrder,
                );
            }

            $lockedOrder->update(['status' => 'dibatalkan']);

            return $lockedOrder->fresh(['items.product', 'fulfillments.seller:id,name']);
        }, 3);
    }
}
