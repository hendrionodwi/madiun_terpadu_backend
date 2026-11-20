<?php

namespace App\Actions\Marketplace;

use App\Enums\OrderFulfillmentStatus;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\User;
use App\Notifications\MarketplaceTransactionNotification;

class SendMarketplaceTransactionNotifications
{
    public function orderCreated(Order $order): void
    {
        $order->loadMissing(['buyer', 'fulfillments.seller']);
        $statusLabel = $this->orderStatusLabel($order->status);

        $this->notify(
            $order->buyer,
            $order,
            'order_created',
            'Pesanan berhasil dibuat',
            "Pesanan {$order->nomor_pesanan} telah dibuat dan berstatus {$statusLabel}.",
            'buyer',
        );

        foreach ($this->sellers($order) as $seller) {
            $this->notify(
                $seller,
                $order,
                'seller_order_created',
                'Pesanan baru masuk',
                "Pesanan {$order->nomor_pesanan} menunggu tindak lanjut TPS3R.",
                'seller',
            );
        }
    }

    public function orderStatusChanged(Order $order, string $previousStatus): void
    {
        $order->refresh()->loadMissing(['buyer', 'fulfillments.seller']);

        if ($previousStatus === $order->status) {
            return;
        }

        $statusLabel = $this->orderStatusLabel($order->status);
        $buyerTitle = match ($order->status) {
            'dibayar' => 'Pembayaran berhasil',
            'dibatalkan' => 'Pesanan dibatalkan',
            'selesai' => 'Pesanan selesai',
            default => 'Status pesanan diperbarui',
        };
        $sellerTitle = match ($order->status) {
            'dibayar' => 'Pembayaran diterima',
            'dibatalkan' => 'Pesanan dibatalkan',
            'selesai' => 'Pesanan diterima pembeli',
            default => 'Status pesanan diperbarui',
        };

        $this->notify(
            $order->buyer,
            $order,
            'order_status_changed',
            $buyerTitle,
            "Pesanan {$order->nomor_pesanan} kini berstatus {$statusLabel}.",
            'buyer',
            ['previous_status' => $previousStatus],
        );

        foreach ($this->sellers($order) as $seller) {
            $this->notify(
                $seller,
                $order,
                'order_status_changed',
                $sellerTitle,
                "Pesanan {$order->nomor_pesanan} kini berstatus {$statusLabel}.",
                'seller',
                ['previous_status' => $previousStatus],
            );
        }
    }

    public function fulfillmentStatusChanged(
        OrderFulfillment $fulfillment,
        OrderFulfillmentStatus $previousStatus,
    ): void {
        $fulfillment->refresh()->loadMissing(['order.buyer', 'seller']);
        $order = $fulfillment->order;

        if ($previousStatus === $fulfillment->status) {
            return;
        }

        $statusLabel = $fulfillment->status->label();
        $context = [
            'fulfillment_id' => $fulfillment->public_id,
            'previous_status' => $previousStatus->value,
            'tracking_number' => $fulfillment->tracking_number,
        ];

        $this->notify(
            $order->buyer,
            $order,
            'fulfillment_status_changed',
            'Status pengiriman diperbarui',
            "Pesanan {$order->nomor_pesanan} kini {$statusLabel}.",
            'buyer',
            $context,
            $fulfillment->status->value,
            $statusLabel,
        );
        $this->notify(
            $fulfillment->seller,
            $order,
            'fulfillment_status_changed',
            'Status pesanan tersimpan',
            "Pesanan {$order->nomor_pesanan} kini {$statusLabel}.",
            'seller',
            $context,
            $fulfillment->status->value,
            $statusLabel,
        );
    }

    public function courierMarkedDelivered(OrderFulfillment $fulfillment): void
    {
        $fulfillment->refresh()->loadMissing(['order.buyer', 'seller']);
        $order = $fulfillment->order;
        $context = [
            'fulfillment_id' => $fulfillment->public_id,
            'tracking_number' => $fulfillment->tracking_number,
            'delivered_at' => $fulfillment->delivered_at?->toIso8601String(),
        ];

        $this->notify(
            $order->buyer,
            $order,
            'courier_marked_delivered',
            'Kiriman terdeteksi sudah diterima',
            "Kurir menandai pesanan {$order->nomor_pesanan} sudah diterima. Periksa barang dan konfirmasi penerimaan.",
            'buyer',
            $context,
            'dikirim',
            'Menunggu konfirmasi penerimaan',
        );
        $this->notify(
            $fulfillment->seller,
            $order,
            'courier_marked_delivered',
            'Pengiriman terdeteksi selesai',
            "Kurir menandai pesanan {$order->nomor_pesanan} sudah diterima pembeli.",
            'seller',
            $context,
            'dikirim',
            'Menunggu konfirmasi penerimaan',
        );
    }

    /** @return array<int, User> */
    private function sellers(Order $order): array
    {
        return $order->fulfillments
            ->pluck('seller')
            ->filter(fn (?User $seller): bool => $seller !== null)
            ->unique('id')
            ->values()
            ->all();
    }

    /**
     * @param  array<string, scalar|null>  $context
     */
    private function notify(
        User $recipient,
        Order $order,
        string $event,
        string $title,
        string $message,
        string $audience,
        array $context = [],
        ?string $status = null,
        ?string $statusLabel = null,
    ): void {
        $recipient->notify(new MarketplaceTransactionNotification(
            event: $event,
            title: $title,
            message: $message,
            orderNumber: $order->nomor_pesanan,
            status: $status ?? $order->status,
            statusLabel: $statusLabel ?? $this->orderStatusLabel($order->status),
            audience: $audience,
            webUrl: $audience === 'seller'
                ? route('dashboard.tps3r.orders.show', $order)
                : route('marketplace.orders.show', $order),
            context: $context,
        ));
    }

    private function orderStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Menunggu pembayaran',
            'dibayar' => 'Sudah dibayar',
            'diproses' => 'Sedang diproses',
            'siap_dikirim' => 'Siap dikirim',
            'dikirim' => 'Dalam pengiriman',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}
