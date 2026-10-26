<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderFulfillmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Marketplace\CheckoutRequest;
use App\Http\Requests\Api\Marketplace\StoreProductRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PengelolaTps3rProfile;
use App\Models\PointLedger;
use App\Models\User;
use App\Models\WargaProfile;
use App\Models\WasteProduct;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class MarketplaceController extends Controller
{
    /**
     * Get recycled product catalog (MKT-F-001).
     */
    public function indexProducts(Request $request): JsonResponse
    {
        $query = WasteProduct::query()->with('seller:id,name,role');

        if ($request->filled('kategori')) {
            $query->where('kategori', (string) $request->kategori);
        }

        if ($request->filled('search')) {
            $searchTerm = (string) $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nama_produk', 'like', "%{$searchTerm}%")
                    ->orWhere('deskripsi', 'like', "%{$searchTerm}%");
            });
        }

        // Default: only show available items for regular buyers unless requested otherwise
        if (! $request->boolean('include_unavailable')) {
            $query->where('is_available', true)->where('stok', '>', 0);
        }

        $products = $query->latest()->paginate((int) ($request->per_page ?? 12));

        return response()->json([
            'success' => true,
            'message' => 'Katalog produk daur ulang berhasil diambil.',
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Get product details (MKT-F-001).
     */
    public function showProduct(int $id): JsonResponse
    {
        $product = WasteProduct::with('seller:id,name,email,role')->find($id);

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk daur ulang tidak ditemukan.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail produk berhasil diambil.',
            'data' => $product,
        ], Response::HTTP_OK);
    }

    /**
     * Store new recycled product to catalog (MKT-F-001).
     */
    public function storeProduct(StoreProductRequest $request): JsonResponse
    {
        $validated = $request->validated();
        /** @var User $seller */
        $seller = $request->user();

        $product = WasteProduct::create([
            'seller_id' => $seller->id,
            'nama_produk' => $validated['nama_produk'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'kategori' => $validated['kategori'],
            'harga' => (float) $validated['harga'],
            'stok' => (int) $validated['stok'],
            'foto' => $validated['foto'] ?? null,
            'is_available' => $validated['is_available'] ?? true,
        ]);

        $product->load('seller:id,name,role');

        return response()->json([
            'success' => true,
            'message' => 'Produk daur ulang berhasil ditambahkan ke katalog.',
            'data' => $product,
        ], Response::HTTP_CREATED);
    }

    /**
     * Checkout shopping cart (MKT-F-003).
     */
    public function checkout(CheckoutRequest $request): JsonResponse
    {
        $validated = $request->validated();
        /** @var User $buyer */
        $buyer = $request->user();

        $quantitiesByProduct = [];
        foreach ($validated['items'] as $item) {
            $productId = (int) $item['product_id'];
            $quantitiesByProduct[$productId] = ($quantitiesByProduct[$productId] ?? 0) + (int) $item['jumlah'];
        }

        $order = DB::transaction(function () use ($validated, $buyer, $quantitiesByProduct): Order {
            $products = WasteProduct::whereIn('id', array_keys($quantitiesByProduct))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $totalHarga = 0.0;
            $orderItemsToCreate = [];

            foreach ($quantitiesByProduct as $productId => $jumlah) {
                /** @var WasteProduct|null $product */
                $product = $products->get($productId);

                if (! $product || ! $product->is_available) {
                    throw ValidationException::withMessages([
                        'items' => 'Salah satu produk tidak tersedia lagi.',
                    ]);
                }

                if ($product->stok < $jumlah) {
                    throw ValidationException::withMessages([
                        'items' => "Stok produk '{$product->nama_produk}' tidak mencukupi (sisa: {$product->stok}).",
                    ]);
                }

                $subtotal = round($product->harga * $jumlah, 2);
                $totalHarga += $subtotal;
                $orderItemsToCreate[] = [
                    'product' => $product,
                    'jumlah' => $jumlah,
                    'harga_satuan' => $product->harga,
                    'subtotal' => $subtotal,
                ];
            }

            $wargaProfile = null;
            $requiredPoints = 0;
            if ($validated['metode_pembayaran'] === 'poin_sampah') {
                $wargaProfile = WargaProfile::where('user_id', $buyer->id)
                    ->lockForUpdate()
                    ->first();
                $requiredPoints = (int) ceil($totalHarga);

                if (! $wargaProfile || $wargaProfile->saldo_poin < $requiredPoints) {
                    $saldoSaatIni = $wargaProfile ? $wargaProfile->saldo_poin : 0;

                    throw ValidationException::withMessages([
                        'metode_pembayaran' => "Saldo poin Anda ({$saldoSaatIni} poin) tidak mencukupi untuk pembayaran sebesar {$requiredPoints} poin.",
                    ]);
                }
            }

            $sellerIds = [];
            foreach ($orderItemsToCreate as $itemData) {
                $sellerIds[$itemData['product']->seller_id] = true;
            }

            if ($validated['metode_pembayaran'] === 'midtrans') {
                if (count($sellerIds) > 1) {
                    throw ValidationException::withMessages([
                        'items' => 'Untuk pembayaran via Midtrans, produk dalam satu transaksi harus berasal dari fasilitas TPS3R yang sama.',
                    ]);
                }

                $sellerId = (int) array_key_first($sellerIds);
                $seller = User::with('pengelolaTps3rProfile')->find($sellerId);
                $tps3rProfile = $seller?->pengelolaTps3rProfile;

                if (! $tps3rProfile || ! $tps3rProfile->isMidtransConfigured()) {
                    $namaTps = $tps3rProfile?->nama_tps3r ?? 'penjual';
                    throw ValidationException::withMessages([
                        'metode_pembayaran' => "Fasilitas TPS3R {$namaTps} belum mengonfigurasi atau mengaktifkan gateway pembayaran online Midtrans.",
                    ]);
                }
            }

            $nomorPesanan = 'ORD-'.date('Ymd').'-'.strtoupper(Str::random(6));

            $order = Order::create([
                'nomor_pesanan' => $nomorPesanan,
                'buyer_id' => $buyer->id,
                'total_harga' => $totalHarga,
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'status' => $validated['metode_pembayaran'] === 'poin_sampah' ? 'dibayar' : 'pending',
                'alamat_pengiriman' => $validated['alamat_pengiriman'],
                'catatan' => $validated['catatan'] ?? null,
            ]);

            foreach ($orderItemsToCreate as $itemData) {
                /** @var WasteProduct $product */
                $product = $itemData['product'];
                $jumlah = $itemData['jumlah'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'jumlah' => $jumlah,
                    'harga_satuan' => $itemData['harga_satuan'],
                    'subtotal' => $itemData['subtotal'],
                ]);

                $product->decrement('stok', $jumlah);
            }

            $order->fulfillments()->createMany(
                array_map(
                    fn (int $sellerId): array => [
                        'seller_id' => $sellerId,
                        'status' => OrderFulfillmentStatus::Menunggu,
                    ],
                    array_keys($sellerIds),
                ),
            );

            if ($wargaProfile) {
                PointLedger::record(
                    $buyer->id,
                    'debit',
                    'belanja_marketplace',
                    $requiredPoints,
                    "Pembayaran pesanan marketplace {$order->nomor_pesanan}",
                    $order
                );
            }

            if ($validated['metode_pembayaran'] === 'midtrans') {
                $sellerId = (int) array_key_first($sellerIds);
                $seller = User::with('pengelolaTps3rProfile')->find($sellerId);
                /** @var PengelolaTps3rProfile $tps3rProfile */
                $tps3rProfile = $seller->pengelolaTps3rProfile;

                try {
                    $snapData = app(MidtransService::class)->createSnapTransaction($order, $tps3rProfile);
                    $order->update([
                        'snap_token' => $snapData['token'],
                        'snap_redirect_url' => $snapData['redirect_url'],
                    ]);
                } catch (\Throwable $e) {
                    throw ValidationException::withMessages([
                        'metode_pembayaran' => 'Gagal menghubungkan gateway pembayaran Midtrans: '.$e->getMessage(),
                    ]);
                }
            }

            return $order;
        });

        $order->load(['items.product', 'buyer:id,name,email,phone_number', 'fulfillments.seller:id,name']);

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibuat (checkout sukses).',
            'data' => $order,
        ], Response::HTTP_CREATED);
    }

    /**
     * Get user's orders (MKT-F-003).
     */
    public function indexOrders(Request $request): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();

        $query = Order::query()->with(['items.product', 'fulfillments.seller:id,name']);

        if (! $currentUser->isAdminDlh()) {
            $query->where('buyer_id', $currentUser->id);
        }

        $orders = $query->latest()->paginate((int) ($request->per_page ?? 10));

        return response()->json([
            'success' => true,
            'message' => 'Daftar pesanan berhasil diambil.',
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Show order detail.
     */
    public function showOrder(Request $request, int $id): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();

        $order = Order::with([
            'items.product',
            'buyer:id,name,email,phone_number',
            'fulfillments.seller:id,name',
        ])->find($id);

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan.',
            ], Response::HTTP_NOT_FOUND);
        }

        if (! $currentUser->isAdminDlh() && $order->buyer_id !== $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.',
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail pesanan berhasil diambil.',
            'data' => $order,
        ], Response::HTTP_OK);
    }

    /**
     * Buyer cancels an order (only allowed if not yet shipped/completed).
     */
    public function cancelOrder(Request $request, int $id): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();

        $order = Order::with(['items.product', 'fulfillments'])->find($id);

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan.',
            ], Response::HTTP_NOT_FOUND);
        }

        if (! $currentUser->isAdminDlh() && $order->buyer_id !== $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.',
            ], Response::HTTP_FORBIDDEN);
        }

        if (in_array($order->status, ['siap_dikirim', 'dikirim', 'selesai', 'dibatalkan'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak dapat dibatalkan karena sudah dalam pengiriman atau telah selesai.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order = DB::transaction(function () use ($order, $request): Order {
            $lockedOrder = Order::with(['items.product', 'fulfillments'])
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($lockedOrder->status, ['siap_dikirim', 'dikirim', 'selesai', 'dibatalkan'], true)) {
                throw ValidationException::withMessages([
                    'order' => ['Pesanan tidak dapat dibatalkan karena sudah dalam pengiriman, selesai, atau telah dibatalkan.'],
                ]);
            }

            $alasan = $request->input('alasan', 'Dibatalkan oleh pembeli');

            foreach ($lockedOrder->fulfillments as $fulfillment) {
                if ($fulfillment->status !== OrderFulfillmentStatus::Dibatalkan) {
                    $fulfillment->update([
                        'status' => OrderFulfillmentStatus::Dibatalkan,
                        'catatan_status' => $alasan,
                        'cancelled_at' => now(),
                    ]);
                }
            }

            foreach ($lockedOrder->items as $item) {
                if ($item->product) {
                    $item->product->increment('stok', $item->jumlah);
                }
            }

            if ($lockedOrder->metode_pembayaran === 'poin_sampah') {
                PointLedger::record(
                    $lockedOrder->buyer_id,
                    'kredit',
                    'refund_marketplace',
                    (int) ceil($lockedOrder->total_harga),
                    "Pengembalian poin pembatalan pesanan {$lockedOrder->nomor_pesanan}",
                    $lockedOrder
                );
            }

            $lockedOrder->update(['status' => 'dibatalkan']);

            return $lockedOrder;
        }, 3);

        $order->load(['items.product', 'fulfillments.seller:id,name']);

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibatalkan. Stok produk dan saldo poin telah dikembalikan.',
            'data' => $order,
        ], Response::HTTP_OK);
    }

    /**
     * Buyer confirms order received (delivery completed).
     */
    public function confirmReceived(Request $request, int $id): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();

        $order = Order::with('fulfillments')->find($id);

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan.',
            ], Response::HTTP_NOT_FOUND);
        }

        if (! $currentUser->isAdminDlh() && $order->buyer_id !== $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($order->status === 'selesai') {
            return response()->json([
                'success' => true,
                'message' => 'Pesanan sudah berstatus selesai.',
                'data' => $order,
            ], Response::HTTP_OK);
        }

        if ($order->status === 'dibatalkan') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan yang telah dibatalkan tidak dapat dikonfirmasi.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::transaction(function () use ($order) {
            foreach ($order->fulfillments as $fulfillment) {
                if ($fulfillment->status !== OrderFulfillmentStatus::Dibatalkan) {
                    $fulfillment->update([
                        'status' => OrderFulfillmentStatus::Selesai,
                        'completed_at' => now(),
                        'catatan_status' => 'Pesanan telah diterima dengan baik oleh pembeli.',
                    ]);
                }
            }

            $order->update(['status' => 'selesai']);
        });

        $order->load(['items.product', 'fulfillments.seller:id,name']);

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dikonfirmasi telah diterima. Transaksi selesai.',
            'data' => $order,
        ], Response::HTTP_OK);
    }

    /**
     * Webhook notification handler from Midtrans.
     */
    public function handleMidtransNotification(Request $request, MidtransService $midtransService): JsonResponse
    {
        $payload = $request->all();
        $orderNumber = $payload['order_id'] ?? null;

        if (! $orderNumber) {
            return response()->json([
                'success' => false,
                'message' => 'Order ID is required in notification payload.',
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var Order|null $order */
        $order = Order::with(['items.product.seller.pengelolaTps3rProfile', 'fulfillments'])
            ->where('nomor_pesanan', $orderNumber)
            ->first();

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan.',
            ], Response::HTTP_NOT_FOUND);
        }

        /** @var WasteProduct|null $firstProduct */
        $firstProduct = $order->items->first()?->product;
        $tps3rProfile = $firstProduct?->seller?->pengelolaTps3rProfile;

        if (! $tps3rProfile || empty($tps3rProfile->midtrans_server_key)) {
            return response()->json([
                'success' => false,
                'message' => 'Kredensial Midtrans fasilitas TPS3R penjual tidak ditemukan.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! $midtransService->verifySignature($payload, (string) $tps3rProfile->midtrans_server_key)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature key.',
            ], Response::HTTP_FORBIDDEN);
        }

        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = (string) ($payload['fraud_status'] ?? '');

        DB::transaction(function () use ($order, $transactionStatus, $fraudStatus, $payload) {
            $order->update(['payment_gateway_response' => $payload]);

            if ($transactionStatus === 'capture') {
                if ($fraudStatus === 'accept') {
                    $order->update(['status' => 'dibayar', 'paid_at' => now()]);
                }
            } elseif ($transactionStatus === 'settlement') {
                $order->update(['status' => 'dibayar', 'paid_at' => now()]);
            } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'], true)) {
                if ($order->status !== 'dibatalkan') {
                    // Kembalikan stok produk jika dibatalkan/kadaluarsa oleh Midtrans
                    foreach ($order->items as $item) {
                        if ($item->product) {
                            $item->product->increment('stok', $item->jumlah);
                        }
                    }
                    $order->update(['status' => 'dibatalkan']);
                    foreach ($order->fulfillments as $f) {
                        $f->update([
                            'status' => OrderFulfillmentStatus::Dibatalkan,
                            'catatan_status' => "Pembayaran Midtrans {$transactionStatus}.",
                            'cancelled_at' => now(),
                        ]);
                    }
                }
            } elseif ($transactionStatus === 'pending') {
                $order->update(['status' => 'pending']);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi Midtrans berhasil diproses.',
        ], Response::HTTP_OK);
    }
}
