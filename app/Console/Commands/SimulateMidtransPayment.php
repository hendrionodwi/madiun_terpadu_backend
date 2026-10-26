<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\V1\MarketplaceController;
use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class SimulateMidtransPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'midtrans:simulate 
                            {order_number? : Nomor pesanan (misal: ORD-20261017-ABCDEF), default: pesanan pending terakhir}
                            {--status=settlement : Status transaksi Midtrans (settlement, expire, cancel, deny)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulasi webhook notifikasi Midtrans untuk pengujian pembayaran lokal tanpa Ngrok';

    /**
     * Execute the console command.
     */
    public function handle(MarketplaceController $controller, MidtransService $service): int
    {
        $orderNumber = $this->argument('order_number');
        $transactionStatus = (string) $this->option('status');

        $query = Order::with(['items.product.seller.pengelolaTps3rProfile'])
            ->where('metode_pembayaran', 'midtrans');

        if ($orderNumber) {
            $order = $query->where('nomor_pesanan', $orderNumber)->first();
        } else {
            $order = $query->where('status', 'pending')->latest()->first();
        }

        if (! $order) {
            $this->error('Tidak ditemukan pesanan dengan metode Midtrans yang siap disimulasikan.');

            return self::FAILURE;
        }

        $seller = $order->items->first()?->product?->seller;
        $profile = $seller?->pengelolaTps3rProfile;

        if (! $profile || empty($profile->midtrans_server_key)) {
            $this->error("TPS3R penjual ({$seller?->name}) belum memiliki Server Key Midtrans terkonfigurasi.");

            return self::FAILURE;
        }

        $statusCode = in_array($transactionStatus, ['settlement', 'capture'], true) ? '200' : '202';
        $grossAmount = number_format((float) $order->total_harga, 2, '.', '');
        $signature = hash('sha512', $order->nomor_pesanan.$statusCode.$grossAmount.$profile->midtrans_server_key);

        $payload = [
            'order_id' => $order->nomor_pesanan,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'transaction_status' => $transactionStatus,
            'fraud_status' => 'accept',
            'signature_key' => $signature,
            'payment_type' => 'bank_transfer',
            'transaction_time' => now()->toDateTimeString(),
        ];

        $this->info("Mengirim simulasi webhook Midtrans untuk pesanan: {$order->nomor_pesanan}...");
        $this->line("Target TPS3R : {$profile->nama_tps3r}");
        $this->line("Status Input : {$transactionStatus}");
        $this->line('Total Bayar  : Rp '.number_format($order->total_harga, 0, ',', '.'));

        $request = Request::create('/api/v1/marketplace/midtrans/notification', 'POST', $payload);
        $response = $controller->handleMidtransNotification($request, $service);

        $data = $response->getData(true);

        if ($response->getStatusCode() === 200 && ($data['success'] ?? false)) {
            $order->refresh();
            $this->newLine();
            $this->info('✓ SUKSES: Webhook notifikasi berhasil diproses!');
            $this->table(
                ['Nomor Pesanan', 'Status Sekarang', 'Waktu Pembayaran'],
                [
                    [$order->nomor_pesanan, $order->status, $order->paid_at ? $order->paid_at->toDateTimeString() : '-'],
                ]
            );

            return self::SUCCESS;
        }

        $this->error('Gagal memproses webhook: '.($data['message'] ?? 'Unknown error'));

        return self::FAILURE;
    }
}
