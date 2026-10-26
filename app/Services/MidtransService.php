<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PengelolaTps3rProfile;
use App\Models\RetributionBill;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MidtransService
{
    /**
     * Get Midtrans Snap API Base URL.
     */
    public static function getSnapBaseUrl(bool $isProduction = false): string
    {
        return $isProduction
            ? 'https://app.midtrans.com/snap/v1'
            : 'https://app.sandbox.midtrans.com/snap/v1';
    }

    /**
     * Create Midtrans Snap Transaction for an order using seller TPS3R's credentials.
     *
     * @return array{token: string, redirect_url: string}
     *
     * @throws Exception
     */
    public function createSnapTransaction(Order $order, PengelolaTps3rProfile $tps3rProfile): array
    {
        if (! $tps3rProfile->isMidtransConfigured()) {
            throw new Exception("Fasilitas TPS3R {$tps3rProfile->nama_tps3r} belum mengonfigurasi gateway pembayaran Midtrans.");
        }

        $serverKey = (string) $tps3rProfile->midtrans_server_key;
        $isProduction = (bool) $tps3rProfile->midtrans_is_production;
        $url = self::getSnapBaseUrl($isProduction).'/transactions';

        $order->loadMissing(['buyer', 'items.product']);

        $itemDetails = [];
        foreach ($order->items as $item) {
            $itemDetails[] = [
                'id' => (string) $item->product_id,
                'price' => (int) round($item->harga_satuan),
                'quantity' => (int) $item->jumlah,
                'name' => Str::limit($item->product->nama_produk, 50, ''),
            ];
        }

        $grossAmount = (int) ceil($order->total_harga);

        $payload = [
            'transaction_details' => [
                'order_id' => $order->nomor_pesanan,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $order->buyer->name,
                'email' => $order->buyer->email,
                'phone' => $order->buyer->phone_number,
                'shipping_address' => [
                    'first_name' => $order->buyer->name,
                    'phone' => $order->buyer->phone_number,
                    'address' => $order->alamat_pengiriman,
                    'city' => 'Kota Madiun',
                ],
            ],
            'item_details' => $itemDetails,
            'callbacks' => [
                'finish' => route('dashboard.tps3r.orders'),
            ],
        ];

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->asJson()
            ->timeout(15)
            ->post($url, $payload);

        if (! $response->successful()) {
            $errorMessage = $response->json('error_messages.0') ?? $response->json('message') ?? 'Gagal membuat transaksi Midtrans.';
            Log::error('Midtrans Snap Transaction Error', [
                'order_id' => $order->nomor_pesanan,
                'tps3r_id' => $tps3rProfile->id,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            throw new Exception("Midtrans error ({$response->status()}): {$errorMessage}");
        }

        $data = $response->json();

        return [
            'token' => (string) $data['token'],
            'redirect_url' => (string) $data['redirect_url'],
        ];
    }

    /**
     * Verify Midtrans webhook notification signature key.
     */
    /** @param array<string, mixed> $payload */
    public function verifySignature(array $payload, string $serverKey): bool
    {
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $receivedSignature = (string) ($payload['signature_key'] ?? '');

        if (empty($orderId) || empty($statusCode) || empty($grossAmount) || empty($receivedSignature)) {
            return false;
        }

        $rawSignature = $orderId.$statusCode.$grossAmount.$serverKey;
        $expectedSignature = hash('sha512', $rawSignature);

        return hash_equals($expectedSignature, $receivedSignature);
    }

    public function retributionServerKey(): string
    {
        return trim((string) config('services.midtrans.server_key', ''));
    }

    /**
     * Test connection for given Midtrans Server Key by pinging the Snap API.
     *
     * @return array{success: bool, message: string}
     */
    public function testConnection(string $serverKey, bool $isProduction = false): array
    {
        $url = self::getSnapBaseUrl($isProduction).'/transactions';

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->asJson()
                ->timeout(10)
                ->post($url, [
                    'transaction_details' => [
                        'order_id' => 'TEST-PING-'.Str::upper(Str::random(6)),
                        'gross_amount' => 10000,
                    ],
                ]);

            if ($response->status() === 201) {
                return [
                    'success' => true,
                    'message' => 'Koneksi ke Midtrans berhasil! Kunci API aktif dan dapat digunakan untuk transaksi.',
                ];
            }

            if ($response->status() === 401) {
                return [
                    'success' => false,
                    'message' => 'Autentikasi gagal (401 Unauthorized). Server Key tidak valid atau tidak cocok dengan environment ('.($isProduction ? 'Production' : 'Sandbox').').',
                ];
            }

            $errorMessage = $response->json('error_messages.0') ?? $response->json('message') ?? 'Koneksi gagal direspons Midtrans.';

            return [
                'success' => false,
                'message' => "Midtrans mengembalikan status {$response->status()}: {$errorMessage}",
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal menghubungi server Midtrans: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Create Midtrans Snap Transaction for a waste retribution bill.
     *
     * @return array{token: string, redirect_url: string}
     *
     * @throws Exception
     */
    public function createRetributionSnapTransaction(RetributionBill $bill): array
    {
        $serverKey = $this->retributionServerKey();
        $isProduction = (bool) config('services.midtrans.is_production', false);
        $allowMock = (bool) config('services.midtrans.allow_mock', false);
        $url = self::getSnapBaseUrl($isProduction).'/transactions';

        $bill->loadMissing('warga');

        $grossAmount = (int) ceil($bill->nominal_dibayar);
        if ($grossAmount <= 0) {
            throw new Exception('Nominal pembayaran retribusi via gateway harus lebih besar dari 0.');
        }

        if ($serverKey === '') {
            if ($allowMock && app()->environment(['local', 'testing'])) {
                return $this->mockRetributionTransaction();
            }

            throw new Exception('Server Key Midtrans retribusi belum dikonfigurasi.');
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $bill->midtrans_order_id ?? $bill->nomor_tagihan,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $bill->warga->name,
                'email' => $bill->warga->email,
                'phone' => $bill->warga->phone_number ?? '081234567890',
            ],
            'item_details' => [
                [
                    'id' => 'RET-'.sprintf('%04d%02d', $bill->periode_tahun, $bill->periode_bulan),
                    'price' => $grossAmount,
                    'quantity' => 1,
                    'name' => Str::limit("Retribusi Sampah {$bill->periode_label}", 50, ''),
                ],
            ],
            'callbacks' => [
                'finish' => url('/dashboard/warga'),
            ],
        ];

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->asJson()
                ->timeout(10)
                ->post($url, $payload);

            if ($response->successful()) {
                return [
                    'token' => $response->json('token'),
                    'redirect_url' => $response->json('redirect_url'),
                ];
            }

            if ($allowMock && app()->environment(['local', 'testing'])) {
                return $this->mockRetributionTransaction();
            }

            $errorMessage = $response->json('error_messages.0') ?? $response->json('message') ?? 'Gagal membuat transaksi Midtrans.';
            throw new Exception("Midtrans error ({$response->status()}): {$errorMessage}");
        } catch (Exception $e) {
            if ($allowMock && app()->environment(['local', 'testing'])) {
                return $this->mockRetributionTransaction();
            }

            Log::error('Midtrans Retribution Snap Error', [
                'bill_id' => $bill->id,
                'nomor_tagihan' => $bill->nomor_tagihan,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * @return array{token: string, redirect_url: string}
     */
    private function mockRetributionTransaction(): array
    {
        $mockToken = 'snap-token-ret-'.Str::random(16);

        return [
            'token' => $mockToken,
            'redirect_url' => "https://app.sandbox.midtrans.com/snap/v2/vtweb/{$mockToken}",
        ];
    }
}
