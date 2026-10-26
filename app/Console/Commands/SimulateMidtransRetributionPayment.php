<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\V1\RetributionController;
use App\Models\RetributionBill;
use App\Services\MidtransService;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class SimulateMidtransRetributionPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'midtrans:simulate-retribution
                            {nomor_tagihan : Nomor tagihan retribusi (e.g. RET-202610-1-A1B2)}
                            {--status=settlement : Status transaksi Midtrans (settlement, expire, cancel, deny)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulasikan webhook notifikasi Midtrans untuk pembayaran retribusi sampah (TRK-F-013)';

    /**
     * Execute the console command.
     */
    public function handle(MidtransService $service, RetributionController $controller): int
    {
        $nomorTagihan = (string) $this->argument('nomor_tagihan');
        $status = (string) $this->option('status');

        $bill = RetributionBill::where('nomor_tagihan', $nomorTagihan)->first();

        if (! $bill) {
            $this->error("Tagihan retribusi dengan nomor {$nomorTagihan} tidak ditemukan.");

            return self::FAILURE;
        }

        $grossAmount = (string) (int) ceil($bill->nominal_dibayar > 0 ? $bill->nominal_dibayar : $bill->nominal_tagihan);
        $statusCode = match ($status) {
            'settlement' => '200',
            'capture' => '200',
            'pending' => '201',
            'deny' => '202',
            'expire' => '407',
            'cancel' => '202',
            default => '200',
        };

        $serverKey = $service->retributionServerKey();
        if ($serverKey === '') {
            $this->error('MIDTRANS_SERVER_KEY belum dikonfigurasi. Simulasi webhook tidak dapat ditandatangani.');

            return self::FAILURE;
        }
        $signature = hash('sha512', $bill->nomor_tagihan.$statusCode.$grossAmount.$serverKey);

        $payload = [
            'order_id' => $bill->midtrans_order_id ?? $bill->nomor_tagihan,
            'transaction_status' => $status,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'payment_type' => 'qris',
            'transaction_time' => now()->format('Y-m-d H:i:s'),
            'settlement_time' => $status === 'settlement' ? now()->format('Y-m-d H:i:s') : null,
            'fraud_status' => 'accept',
        ];

        $this->info("Mengirim simulasi webhook Midtrans untuk tagihan: {$bill->nomor_tagihan} (status: {$status})...");

        $request = Request::create('/api/v1/retributions/midtrans/notification', 'POST', $payload);
        $response = $controller->handleMidtransNotification($request, $service);

        $this->info('Respons: '.$response->getContent());
        $this->info('Status tagihan saat ini: '.$bill->fresh()->status);

        return self::SUCCESS;
    }
}
