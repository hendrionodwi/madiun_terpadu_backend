<?php

namespace App\Actions\Marketplace;

use App\Enums\OrderFulfillmentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PengelolaTps3rProfile;
use App\Models\PointLedger;
use App\Models\User;
use App\Models\WargaProfile;
use App\Models\WasteProduct;
use App\Services\MarketplaceShippingService;
use App\Services\MidtransService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class CreateMarketplaceOrder
{
    public function __construct(
        public MidtransService $midtransService,
        public CancelMarketplaceOrder $cancelMarketplaceOrder,
        public MarketplaceShippingService $marketplaceShippingService,
        public SendMarketplaceTransactionNotifications $transactionNotifications,
    ) {}

    /**
     * @param array{
     *     items: array<int, array{product_id: int, jumlah: int}>,
     *     metode_pembayaran: string,
     *     metode_pengiriman?: string,
     *     alamat_pengiriman?: string|null,
     *     shipping_source?: string|null,
     *     destination_kelurahan?: string|null,
     *     destination_kecamatan?: string|null,
     *     rajaongkir_destination_id?: int|null,
     *     shipping_courier?: string|null,
     *     shipping_service?: string|null,
     *     catatan?: string|null
     * } $data
     */
    public function handle(User $buyer, array $data): Order
    {
        $quantitiesByProduct = [];
        foreach ($data['items'] as $item) {
            $productId = (int) $item['product_id'];
            $quantitiesByProduct[$productId] = ($quantitiesByProduct[$productId] ?? 0) + (int) $item['jumlah'];
        }

        $quoteProducts = WasteProduct::query()
            ->with('seller:id,is_active')
            ->whereIn('id', array_keys($quantitiesByProduct))
            ->get();

        if ($quoteProducts->count() !== count($quantitiesByProduct)) {
            throw ValidationException::withMessages(['items' => 'Salah satu produk tidak ditemukan.']);
        }

        foreach ($quoteProducts as $product) {
            if (! $product->is_available || ! $product->seller->is_active) {
                throw ValidationException::withMessages(['items' => 'Salah satu produk atau toko tidak tersedia.']);
            }
        }

        $shippingQuote = $this->marketplaceShippingService->resolve($quoteProducts, $quantitiesByProduct, $data);

        $order = DB::transaction(function () use ($buyer, $data, $quantitiesByProduct, $shippingQuote): Order {
            $products = WasteProduct::query()
                ->with('seller:id,is_active')
                ->whereIn('id', array_keys($quantitiesByProduct))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotalProduk = 0.0;
            $orderItems = [];

            foreach ($quantitiesByProduct as $productId => $quantity) {
                /** @var WasteProduct|null $product */
                $product = $products->get($productId);

                if (! $product || ! $product->is_available) {
                    throw ValidationException::withMessages(['items' => 'Salah satu produk tidak tersedia lagi.']);
                }

                if (! $product->seller->is_active) {
                    throw ValidationException::withMessages([
                        'items' => "Toko TPS3R untuk produk '{$product->nama_produk}' sedang tidak aktif.",
                    ]);
                }

                if ($product->stok < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => "Stok produk '{$product->nama_produk}' tidak mencukupi (sisa: {$product->stok}).",
                    ]);
                }

                $subtotal = round($product->harga * $quantity, 2);
                $subtotalProduk += $subtotal;
                $orderItems[] = compact('product', 'quantity', 'subtotal');
            }

            $sellerIds = collect($orderItems)->pluck('product.seller_id')->unique()->values();
            if ($sellerIds->count() !== 1 || (int) $sellerIds->first() !== $shippingQuote['seller_id']) {
                throw ValidationException::withMessages([
                    'items' => 'Data produk berubah. Muat ulang checkout sebelum membuat pesanan.',
                ]);
            }

            $shippingMethod = $data['metode_pengiriman'] ?? 'ambil_mandiri';
            $currentWeight = $this->marketplaceShippingService->calculateWeight($products->values(), $quantitiesByProduct);
            if ($currentWeight !== $shippingQuote['weight_grams']) {
                throw ValidationException::withMessages([
                    'items' => 'Berat produk berubah. Hitung ulang ongkir sebelum membuat pesanan.',
                ]);
            }

            $shippingFee = $shippingQuote['fee'];
            $grandTotal = round($subtotalProduk + $shippingFee, 2);
            $sellerId = $shippingQuote['seller_id'];
            $tps3rProfile = $this->resolvePaymentProfile($sellerId, $data['metode_pembayaran']);

            $wargaProfile = null;
            $requiredPoints = 0;
            if ($data['metode_pembayaran'] === 'poin_sampah') {
                $wargaProfile = WargaProfile::query()->where('user_id', $buyer->id)->lockForUpdate()->firstOrFail();
                $requiredPoints = (int) ceil($grandTotal);
                $availablePoints = $wargaProfile->saldo_poin;

                if ($availablePoints < $requiredPoints) {
                    throw ValidationException::withMessages([
                        'metode_pembayaran' => "Saldo poin Anda ({$availablePoints} poin) tidak mencukupi untuk pembayaran sebesar {$requiredPoints} poin.",
                    ]);
                }
            }

            $order = Order::query()->create([
                'nomor_pesanan' => 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
                'buyer_id' => $buyer->id,
                'total_harga' => $grandTotal,
                'metode_pembayaran' => $data['metode_pembayaran'],
                'metode_pengiriman' => $shippingMethod,
                'biaya_pengiriman' => $shippingFee,
                'shipping_source' => $shippingQuote['source'],
                'status' => $data['metode_pembayaran'] === 'poin_sampah' ? 'dibayar' : 'pending',
                'alamat_pengiriman' => $shippingMethod === 'diantar' ? ($data['alamat_pengiriman'] ?? null) : null,
                'destination_kelurahan' => $shippingQuote['destination_kelurahan'],
                'destination_kecamatan' => $shippingQuote['destination_kecamatan'],
                'rajaongkir_destination_id' => $shippingQuote['rajaongkir_destination_id'],
                'shipping_courier' => $shippingQuote['courier'],
                'shipping_service' => $shippingQuote['service'],
                'shipping_etd' => $shippingQuote['etd'],
                'shipping_weight_grams' => $shippingQuote['weight_grams'],
                'catatan' => $data['catatan'] ?? null,
            ]);

            foreach ($orderItems as $itemData) {
                /** @var WasteProduct $product */
                $product = $itemData['product'];
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'jumlah' => $itemData['quantity'],
                    'harga_satuan' => $product->harga,
                    'subtotal' => $itemData['subtotal'],
                ]);
                $product->decrement('stok', $itemData['quantity']);
            }

            $order->fulfillments()->create([
                'seller_id' => $sellerId,
                'status' => OrderFulfillmentStatus::Menunggu,
            ]);

            if ($wargaProfile) {
                PointLedger::record(
                    $buyer->id,
                    'debit',
                    'belanja_marketplace',
                    $requiredPoints,
                    "Pembayaran pesanan marketplace {$order->nomor_pesanan}",
                    $order,
                );
            }

            if ($tps3rProfile) {
                $order->setRelation('paymentProfile', $tps3rProfile);
            }

            return $order;
        }, 3);

        if ($data['metode_pembayaran'] === 'midtrans') {
            try {
                /** @var PengelolaTps3rProfile $profile */
                $profile = $order->getRelation('paymentProfile');
                $snapData = $this->midtransService->createSnapTransaction($order, $profile);
                $order->update([
                    'snap_token' => $snapData['token'],
                    'snap_redirect_url' => $snapData['redirect_url'],
                ]);
            } catch (Throwable $exception) {
                Log::error('Marketplace checkout could not initialize Midtrans.', [
                    'order_id' => $order->nomor_pesanan,
                    'error' => $exception->getMessage(),
                ]);
                $this->cancelMarketplaceOrder->handle($order, 'Gateway pembayaran gagal diinisialisasi.', false);
                throw ValidationException::withMessages([
                    'metode_pembayaran' => 'Pembayaran online sedang tidak dapat diproses. Silakan coba kembali.',
                ]);
            }
        }

        $order = $order->fresh(['items.product', 'buyer:id,name,email,phone_number', 'fulfillments.seller:id,name']);
        $this->transactionNotifications->orderCreated($order);

        return $order;
    }

    private function resolvePaymentProfile(int $sellerId, string $paymentMethod): ?PengelolaTps3rProfile
    {
        if ($paymentMethod !== 'midtrans') {
            return null;
        }

        $profile = User::query()->with('pengelolaTps3rProfile')->find($sellerId)?->pengelolaTps3rProfile;
        if (! $profile || ! $profile->isMidtransConfigured()) {
            throw ValidationException::withMessages([
                'metode_pembayaran' => 'Fasilitas TPS3R belum mengaktifkan pembayaran online Midtrans.',
            ]);
        }

        return $profile;
    }
}
