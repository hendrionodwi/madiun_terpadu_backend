<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\RetributionBill;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateMonthlyRetributionBills extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'retribution:generate-bills
                            {--month= : Bulan tagihan (1-12, default bulan ini)}
                            {--year= : Tahun tagihan (default tahun ini)}
                            {--nominal=10000 : Tarif retribusi bulanan per warga dalam rupiah}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Terbitkan tagihan retribusi sampah bulanan untuk seluruh warga aktif Kota Madiun (TRK-F-013)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $defaultTariff = (float) RetributionBill::getConfig('monthly_tariff', 10000);
        $defaultDueDay = (int) RetributionBill::getConfig('standard_due_day', 20);

        $month = (int) ($this->option('month') ?: now()->month);
        $year = (int) ($this->option('year') ?: now()->year);
        $nominal = (float) ($this->option('nominal') ?: $defaultTariff);

        if ($month < 1 || $month > 12) {
            $this->error('Bulan harus di antara 1 dan 12.');

            return self::FAILURE;
        }

        $wargaList = User::query()
            ->where('role', UserRole::Warga)
            ->where('is_active', true)
            ->get();

        if ($wargaList->isEmpty()) {
            $this->warn('Tidak ada akun warga aktif yang ditemukan.');

            return self::SUCCESS;
        }

        $dueDate = Carbon::create($year, $month, min($defaultDueDay, 28))->endOfDay();
        if ($dueDate->isPast() && now()->month === $month && now()->year === $year) {
            $dueDate = now()->addDays(7)->endOfDay();
        }

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($wargaList as $warga) {
            $bill = RetributionBill::firstOrCreate(
                [
                    'warga_id' => $warga->id,
                    'periode_bulan' => $month,
                    'periode_tahun' => $year,
                ],
                [
                    'nomor_tagihan' => RetributionBill::generateNomorTagihan($year, $month, $warga->id),
                    'nominal_tagihan' => $nominal,
                    'poin_digunakan' => 0,
                    'nominal_potongan_poin' => 0,
                    'nominal_dibayar' => $nominal,
                    'metode_pembayaran' => 'belum_dipilih',
                    'status' => 'belum_dibayar',
                    'jatuh_tempo' => $dueDate->toDateString(),
                    'catatan' => "Iuran retribusi pelayanan persampahan/kebersihan Kota Madiun Periode {$month}/{$year}",
                ]
            );

            if ($bill->wasRecentlyCreated) {
                $createdCount++;
            } else {
                $skippedCount++;
            }
        }

        $this->info("Berhasil menerbitkan {$createdCount} tagihan retribusi untuk periode {$month}/{$year}. ({$skippedCount} warga sudah memiliki tagihan).");

        return self::SUCCESS;
    }
}
