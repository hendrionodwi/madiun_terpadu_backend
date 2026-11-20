<?php

namespace App\Console\Commands;

use App\Actions\Marketplace\SendMarketplaceTransactionNotifications;
use App\Enums\OrderFulfillmentStatus;
use App\Models\Order;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('marketplace:simulate-delivery {order : Nomor pesanan marketplace} {--minutes= : Menit sebelum auto-selesai}')]
#[Description('Simulasikan paket RajaOngkir telah diterima tanpa membuat pengiriman nyata.')]
class SimulateMarketplaceDelivery extends Command
{
    public function handle(SendMarketplaceTransactionNotifications $transactionNotifications): int
    {
        if (app()->isProduction()) {
            $this->components->error('Simulator pengiriman tidak dapat dijalankan di production.');

            return self::FAILURE;
        }

        $order = Order::query()
            ->with('fulfillments')
            ->where('nomor_pesanan', (string) $this->argument('order'))
            ->first();

        if (! $order) {
            $this->components->error('Pesanan tidak ditemukan.');

            return self::FAILURE;
        }

        if ($order->shipping_source !== 'rajaongkir') {
            $this->components->error('Simulator ini hanya untuk pengiriman RajaOngkir.');

            return self::FAILURE;
        }

        $minutes = $this->option('minutes') !== null
            ? (int) $this->option('minutes')
            : (int) config('services.rajaongkir.simulation_auto_complete_minutes', 2);

        if ($minutes < 1 || $minutes > 60) {
            $this->components->error('Durasi simulasi harus antara 1 dan 60 menit.');

            return self::FAILURE;
        }

        $fulfillments = $order->fulfillments->filter(
            fn ($fulfillment): bool => $fulfillment->status === OrderFulfillmentStatus::Dikirim,
        );

        if ($fulfillments->isEmpty()) {
            $this->components->error('Pesanan harus berstatus dikirim sebelum dapat disimulasikan diterima.');

            return self::FAILURE;
        }

        foreach ($fulfillments as $fulfillment) {
            $fulfillment->update([
                'courier_status' => 'DELIVERED',
                'courier_status_description' => 'Paket diterima (simulasi lokal).',
                'tracking_checked_at' => now(),
                'next_tracking_check_at' => null,
                'tracking_last_error' => null,
                'delivered_at' => now(),
                'auto_complete_at' => now()->addMinutes($minutes),
            ]);
            $transactionNotifications->courierMarkedDelivered($fulfillment);
        }

        $this->components->info("Simulasi aktif. Pesanan otomatis selesai dalam {$minutes} menit.");

        return self::SUCCESS;
    }
}
