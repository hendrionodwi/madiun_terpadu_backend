<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ClearOperationalData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-operational-data {--keep-routes : Pertahankan data rute master}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengosongkan semua data operasional sampah (laporan, penjemputan, rute, setoran TPS3R, penarikan poin)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai pembersihan data operasional sistem...');

        $tables = [
            'waste_weighings',
            'pickup_schedules',
            'tps3r_waste_deposits',
            'waste_processing_records',
            'complaint_verifications',
            'complaints',
            'environmental_reports',
            'fund_withdrawal_requests',
            'activity_logs',
            'order_fulfillments',
            'order_items',
            'orders',
        ];

        if (! $this->option('keep-routes')) {
            $tables[] = 'route_assignments';
        }

        Schema::disableForeignKeyConstraints();

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $countBefore = DB::table($table)->count();
                DB::table($table)->truncate();
                $this->line(" - Tabel <comment>{$table}</comment>: {$countBefore} entri dibersihkan.");
            }
        }

        Schema::enableForeignKeyConstraints();

        // Reset status dan penugasan petugas lapangan
        if (! $this->option('keep-routes')) {
            DB::table('petugas_lapangan_profiles')->update([
                'id_wilayah_rute' => null,
                'nama_wilayah_tugas' => null,
                'status_tugas' => 'standby',
            ]);
            $this->line(' - Profil Petugas Lapangan: Status tugas di-reset ke standby & rute dikosongkan.');
        }

        // Reset saldo poin warga
        DB::table('warga_profiles')->update([
            'saldo_poin' => 0,
        ]);
        $this->line(' - Profil Warga: Saldo poin di-reset ke 0.');

        // Bersihkan file unggahan sementara di storage publik
        $files = Storage::disk('public')->allFiles();
        $cleanedFiles = 0;
        foreach ($files as $file) {
            if ($file === '.gitignore' || str_starts_with($file, 'products/')) {
                continue;
            }
            Storage::disk('public')->delete($file);
            $cleanedFiles++;
        }
        $this->line(" - Storage File Unggahan: {$cleanedFiles} file bukti/laporan dibersihkan.");

        $this->info('✅ Seluruh data operasional berhasil dikosongkan.');

        return Command::SUCCESS;
    }
}
