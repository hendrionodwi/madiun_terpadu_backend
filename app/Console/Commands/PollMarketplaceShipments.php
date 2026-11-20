<?php

namespace App\Console\Commands;

use App\Actions\Marketplace\SendMarketplaceTransactionNotifications;
use App\Enums\OrderFulfillmentStatus;
use App\Exceptions\RajaOngkirException;
use App\Models\OrderFulfillment;
use App\Services\RajaOngkirService;
use Carbon\CarbonInterface;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use InvalidArgumentException;
use Throwable;

#[Signature('marketplace:poll-shipments')]
#[Description('Perbarui status resi aktif melalui API tracking RajaOngkir.')]
class PollMarketplaceShipments extends Command
{
    public function handle(
        RajaOngkirService $rajaOngkirService,
        SendMarketplaceTransactionNotifications $transactionNotifications,
    ): int {
        $batchSize = max(1, (int) config('services.rajaongkir.tracking_batch_size', 20));
        $pollMinutes = max(15, (int) config('services.rajaongkir.tracking_poll_minutes', 180));
        $dailyLimit = max(1, (int) config('services.rajaongkir.tracking_daily_limit', 80));
        $rateLimitKey = 'rajaongkir-tracking:'.now()->toDateString();

        $fulfillments = OrderFulfillment::query()
            ->with('order.buyer:id,phone_number')
            ->where('status', OrderFulfillmentStatus::Dikirim->value)
            ->whereNotNull('tracking_number')
            ->whereNull('delivered_at')
            ->where(function ($query): void {
                $query->whereNull('next_tracking_check_at')
                    ->orWhere('next_tracking_check_at', '<=', now());
            })
            ->whereHas('order', fn ($query) => $query->where('shipping_source', 'rajaongkir'))
            ->orderBy('next_tracking_check_at')
            ->orderBy('id')
            ->limit($batchSize)
            ->get();

        $checked = 0;
        $delivered = 0;

        foreach ($fulfillments as $fulfillment) {
            if (RateLimiter::tooManyAttempts($rateLimitKey, $dailyLimit)) {
                $this->components->warn('Batas aman hit tracking harian telah tercapai.');

                break;
            }

            $rateLimitSeconds = max(60, (int) ceil(now()->secondsUntilEndOfDay() + 60));
            RateLimiter::hit($rateLimitKey, $rateLimitSeconds);
            $checked++;

            try {
                $tracking = $rajaOngkirService->trackWaybill(
                    (string) $fulfillment->tracking_number,
                    (string) $fulfillment->order->shipping_courier,
                    $this->lastFivePhoneDigits($fulfillment->order->buyer->phone_number),
                );
            } catch (RajaOngkirException|InvalidArgumentException $exception) {
                $fulfillment->update([
                    'tracking_checked_at' => now(),
                    'next_tracking_check_at' => now()->addMinutes($pollMinutes),
                    'tracking_last_error' => $exception->getMessage(),
                ]);
                $this->components->warn("Resi {$fulfillment->tracking_number} belum dapat diperbarui.");

                continue;
            }

            $attributes = [
                'courier_status' => $tracking['status'] !== ''
                    ? $tracking['status']
                    : $fulfillment->courier_status,
                'courier_status_description' => $tracking['description'] !== ''
                    ? $tracking['description']
                    : $fulfillment->courier_status_description,
                'tracking_checked_at' => now(),
                'next_tracking_check_at' => now()->addMinutes($pollMinutes),
                'tracking_last_error' => null,
            ];

            if ($tracking['delivered']) {
                $deliveredAt = $this->resolveDeliveredAt($tracking['delivered_at']);
                $attributes['delivered_at'] = $deliveredAt;
                $attributes['auto_complete_at'] = $deliveredAt->copy()->addHours(
                    max(1, (int) config('services.rajaongkir.auto_complete_hours', 48)),
                );
                $attributes['next_tracking_check_at'] = null;
                $delivered++;
            }

            $fulfillment->update($attributes);

            if ($tracking['delivered']) {
                $transactionNotifications->courierMarkedDelivered($fulfillment);
            }
        }

        $this->components->info("Pelacakan selesai: {$checked} resi diperiksa, {$delivered} terdeteksi diterima.");

        return self::SUCCESS;
    }

    private function lastFivePhoneDigits(?string $phoneNumber): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phoneNumber);

        return mb_strlen($digits) >= 5 ? mb_substr($digits, -5) : null;
    }

    private function resolveDeliveredAt(?string $value): CarbonInterface
    {
        if ($value === null || trim($value) === '') {
            return now();
        }

        try {
            return Carbon::parse($value, (string) config('app.timezone'));
        } catch (Throwable) {
            return now();
        }
    }
}
