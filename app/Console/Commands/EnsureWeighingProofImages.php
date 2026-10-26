<?php

namespace App\Console\Commands;

use App\Models\WasteWeighing;
use App\Services\WeighingProofService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:ensure-weighing-proof-images')]
#[Description('Ensure all weighing proof image files physically exist on storage disk')]
class EnsureWeighingProofImages extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(WeighingProofService $service): int
    {
        $service->generatePlaceholderImage();
        $weighings = WasteWeighing::with(['warga', 'petugas'])->get();
        $count = 0;

        foreach ($weighings as $w) {
            if ($w->foto_bukti) {
                $service->ensureProofImage($w->foto_bukti, [
                    'berat_kg' => $w->berat_kg,
                    'jenis_sampah' => $w->jenis_sampah,
                    'warga_name' => $w->warga?->name,
                    'petugas_name' => $w->petugas?->name,
                    'timestamp' => $w->created_at?->format('d M Y, H:i').' WIB',
                ]);
                $this->info("Generated image for Weighing ID {$w->id}: {$w->foto_bukti}");
                $count++;
            }
        }

        $this->info("Done! Processed {$count} weighing proof images.");

        return self::SUCCESS;
    }
}
