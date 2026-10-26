<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\BroadcastAnnouncement;
use App\Models\RetributionBill;
use App\Models\User;
use Illuminate\Console\Command;

class SendRetributionReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'retribution:send-reminders
                            {--overdue-only : Hanya kirim pengingat untuk tagihan yang menunggak lewat batas jatuh tempo}
                            {--broadcast : Buat pengumuman broadcast sistem ke seluruh warga di aplikasi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim notifikasi pengingat pembayaran tagihan retribusi kebersihan kepada warga Kota Madiun (TRK-F-013)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $overdueOnly = (bool) $this->option('overdue-only');
        $createBroadcast = (bool) $this->option('broadcast');

        $query = RetributionBill::with('warga')->whereIn('status', RetributionBill::OUTSTANDING_STATUSES);

        if ($overdueOnly) {
            $query->where('jatuh_tempo', '<', now()->toDateString());
        }

        $bills = $query->orderBy('jatuh_tempo')->get();

        if ($bills->isEmpty()) {
            $this->info($overdueOnly
                ? 'Tidak ada tagihan retribusi yang menunggak saat ini.'
                : 'Tidak ada tagihan retribusi berstatus belum dibayar saat ini.');

            return self::SUCCESS;
        }

        $this->info(sprintf(
            'Ditemukan %d tagihan retribusi %s.',
            $bills->count(),
            $overdueOnly ? 'yang menunggak (overdue)' : 'belum dibayar'
        ));

        // Display summary table
        $tableRows = $bills->take(20)->map(function (RetributionBill $bill) {
            return [
                'Nomor Tagihan' => $bill->nomor_tagihan,
                'Warga' => $bill->warga?->name ?? 'N/A',
                'No. HP' => $bill->warga?->phone_number ?? '-',
                'Periode' => $bill->periode_label,
                'Nominal' => 'Rp '.number_format($bill->nominal_tagihan, 0, ',', '.'),
                'Jatuh Tempo' => $bill->jatuh_tempo->format('d/m/Y'),
                'Status' => $bill->isOverdue() ? 'MENUNGGAK' : 'Belum Dibayar',
            ];
        });

        $this->table(['Nomor Tagihan', 'Warga', 'No. HP', 'Periode', 'Nominal', 'Jatuh Tempo', 'Status'], $tableRows);

        if ($bills->count() > 20) {
            $this->comment(sprintf('...dan %d tagihan lainnya.', $bills->count() - 20));
        }

        // Create system broadcast notification if requested
        if ($createBroadcast) {
            $adminUser = User::where('role', UserRole::AdminDlh)->first() ?? User::first();

            if ($adminUser) {
                $title = $overdueOnly
                    ? 'Peringatan Jatuh Tempo: Segera Lunasi Retribusi Sampah'
                    : 'Pengingat Pembayaran Retribusi Kebersihan Kota Madiun';

                $content = $overdueOnly
                    ? 'Yth. Warga Kota Madiun, mohon segera melakukan pelunasan tagihan retribusi pelayanan persampahan Anda yang telah melewati batas jatuh tempo. Keterlambatan pembayaran dapat menyebabkan penangguhan sementara layanan penjemputan sampah harian. Pembayaran dapat dilakukan langsung di aplikasi menggunakan Saldo Poin Bank Sampah TPS3R atau pembayaran digital QRIS/E-Wallet.'
                    : 'Yth. Warga Kota Madiun, tagihan retribusi kebersihan periode berjalan telah diterbitkan. Silakan periksa tagihan Anda di aplikasi Madiun Terpadu dan lakukan pelunasan sebelum jatuh tempo. Anda dapat menukarkan poin sampah Anda untuk mendapatkan potongan hingga 100%.';

                BroadcastAnnouncement::create([
                    'created_by' => $adminUser->id,
                    'title' => $title,
                    'content' => $content,
                    'audience_role' => 'warga',
                    'priority' => $overdueOnly ? 'urgent' : 'high',
                    'is_active' => true,
                    'published_at' => now(),
                    'expires_at' => now()->addDays(7),
                ]);

                $this->info("Siaran pengumuman resmi ke seluruh warga berhasil dipublikasikan: \"{$title}\"");
            }
        }

        $this->info(sprintf('Proses selesai. Total %d pengingat tagihan siap ditindaklanjuti.', $bills->count()));

        return self::SUCCESS;
    }
}
