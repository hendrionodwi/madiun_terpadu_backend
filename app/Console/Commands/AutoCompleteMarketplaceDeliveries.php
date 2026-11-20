<?php

namespace App\Console\Commands;

use App\Actions\Marketplace\TransitionOrderFulfillmentStatus;
use App\Enums\OrderFulfillmentStatus;
use App\Models\OrderFulfillment;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

#[Signature('marketplace:auto-complete-deliveries')]
#[Description('Selesaikan pesanan yang melewati batas konfirmasi setelah diterima kurir.')]
class AutoCompleteMarketplaceDeliveries extends Command
{
    public function handle(TransitionOrderFulfillmentStatus $transition): int
    {
        $fulfillments = OrderFulfillment::query()
            ->where('status', OrderFulfillmentStatus::Dikirim->value)
            ->whereNotNull('delivered_at')
            ->whereNotNull('auto_complete_at')
            ->where('auto_complete_at', '<=', now())
            ->orderBy('auto_complete_at')
            ->orderBy('id')
            ->limit(100)
            ->get();

        $completed = 0;

        foreach ($fulfillments as $fulfillment) {
            try {
                $transition->handle(
                    $fulfillment,
                    OrderFulfillmentStatus::Selesai,
                    'Pesanan diselesaikan otomatis setelah masa konfirmasi penerimaan berakhir.',
                );
                $completed++;
            } catch (ValidationException $exception) {
                $this->components->warn(
                    "Fulfillment {$fulfillment->public_id} dilewati: ".collect($exception->errors())->flatten()->first(),
                );
            }
        }

        $this->components->info("{$completed} fulfillment diselesaikan otomatis.");

        return self::SUCCESS;
    }
}
