<?php

namespace App\Http\Controllers\Web;

use App\Actions\Marketplace\CancelMarketplaceOrder;
use App\Actions\Marketplace\CreateMarketplaceOrder;
use App\Actions\Marketplace\SendMarketplaceTransactionNotifications;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Marketplace\StoreCheckoutRequest;
use App\Http\Requests\Web\Marketplace\UpdateCartRequest;
use App\Models\Order;
use App\Models\Tps3rDeliveryZone;
use App\Models\User;
use App\Models\WasteProduct;
use App\Services\PublicPortalDataService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MarketplaceController extends Controller
{
    private const CART_SESSION_KEY = 'marketplace_cart';

    public function __construct(
        public PublicPortalDataService $portalData,
        public SendMarketplaceTransactionNotifications $transactionNotifications,
    ) {}

    public function showProduct(WasteProduct $product): View
    {
        abort_unless($product->is_available, 404);
        $product->load('seller.pengelolaTps3rProfile');

        $relatedProducts = WasteProduct::query()
            ->where('id', '!=', $product->id)
            ->where('seller_id', $product->seller_id)
            ->where('is_available', true)
            ->where('stok', '>', 0)
            ->with('seller.pengelolaTps3rProfile')
            ->limit(4)
            ->get();

        return view('marketplace.product', $this->viewData([
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]));
    }

    public function cart(Request $request): View
    {
        return view('marketplace.cart', $this->viewData($this->cartData($request)));
    }

    public function addToCart(UpdateCartRequest $request, WasteProduct $product): RedirectResponse
    {
        abort_unless($product->is_available && $product->stok > 0, 404);
        $cart = $request->session()->get(self::CART_SESSION_KEY, []);

        $quantity = min(($cart[$product->public_id] ?? 0) + $request->integer('jumlah'), $product->stok);
        $cart[$product->public_id] = $quantity;
        $request->session()->put(self::CART_SESSION_KEY, $cart);

        return redirect()->route('marketplace.cart')->with('success', 'Produk ditambahkan ke keranjang.');
    }

    public function updateCart(UpdateCartRequest $request, WasteProduct $product): RedirectResponse
    {
        $cart = $request->session()->get(self::CART_SESSION_KEY, []);
        if (! array_key_exists($product->public_id, $cart)) {
            abort(404);
        }

        $cart[$product->public_id] = min($request->integer('jumlah'), max($product->stok, 1));
        $request->session()->put(self::CART_SESSION_KEY, $cart);

        return back()->with('success', 'Jumlah produk diperbarui.');
    }

    public function removeFromCart(Request $request, WasteProduct $product): RedirectResponse
    {
        $cart = $request->session()->get(self::CART_SESSION_KEY, []);
        unset($cart[$product->public_id]);
        $request->session()->put(self::CART_SESSION_KEY, $cart);

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }

    public function checkout(Request $request): View|RedirectResponse
    {
        $allCartData = $this->cartData($request);
        if ($allCartData['items']->isEmpty()) {
            return redirect()->route('marketplace.cart')->withErrors(['cart' => 'Keranjang belanja masih kosong.']);
        }

        $sellerId = $this->resolveCheckoutSellerId($request, $allCartData['items']);
        if ($sellerId === null) {
            return redirect()->route('marketplace.cart')->withErrors([
                'cart' => 'Pilih TPS3R yang ingin diproses terlebih dahulu.',
            ]);
        }
        $cartData = $this->cartData($request, $sellerId);

        /** @var User $user */
        $user = $request->user();
        $user->loadMissing('wargaProfile');
        /** @var array{product: WasteProduct, jumlah: int, subtotal: float} $firstItem */
        $firstItem = $cartData['items']->first();
        $tps3rProfile = $firstItem['product']->seller->pengelolaTps3rProfile;
        $deliveryZones = $tps3rProfile?->deliveryZones()->approved()->orderBy('kecamatan')->orderBy('kelurahan')->get()
            ?? collect();
        $cartWeight = (int) $cartData['items']->sum(
            fn (array $item): int => max(1, $item['product']->weight_grams) * $item['jumlah'],
        );
        $destinationKelurahan = $this->oldString($request, 'destination_kelurahan');
        $destinationKecamatan = $this->oldString($request, 'destination_kecamatan');
        $checkoutConfig = [
            'delivery' => $this->oldString($request, 'metode_pengiriman', 'ambil_mandiri'),
            'source' => $this->oldString($request, 'shipping_source', 'local_tps3r'),
            'payment' => $this->oldString($request, 'metode_pembayaran', 'midtrans'),
            'zones' => $deliveryZones->map(static fn (Tps3rDeliveryZone $zone): array => [
                'key' => $zone->kelurahan.'|'.$zone->kecamatan,
                'kelurahan' => $zone->kelurahan,
                'kecamatan' => $zone->kecamatan,
                'fee' => (int) $zone->delivery_fee,
            ])->values()->all(),
            'selectedZoneKey' => $destinationKelurahan !== '' && $destinationKecamatan !== ''
                ? $destinationKelurahan.'|'.$destinationKecamatan
                : '',
            'destinationKelurahan' => $destinationKelurahan,
            'destinationKecamatan' => $destinationKecamatan,
            'destinationId' => (int) $this->oldString($request, 'rajaongkir_destination_id', '0'),
            'originId' => (int) ($tps3rProfile->rajaongkir_origin_id ?? 0),
            'destinationsUrl' => route('marketplace.shipping.destinations'),
            'quotesUrl' => route('marketplace.shipping.quotes'),
            'csrfToken' => csrf_token(),
            'cartWeight' => $cartWeight,
        ];

        return view('marketplace.checkout', $this->viewData([
            ...$cartData,
            'wargaProfile' => $user->wargaProfile,
            'deliveryZones' => $deliveryZones,
            'checkoutConfig' => $checkoutConfig,
            'checkoutSellerId' => $sellerId,
        ]));
    }

    public function storeCheckout(
        StoreCheckoutRequest $request,
        CreateMarketplaceOrder $createMarketplaceOrder,
    ): RedirectResponse {
        $allCartData = $this->cartData($request);
        $sellerId = $this->resolveCheckoutSellerId($request, $allCartData['items']);
        if ($sellerId === null) {
            return redirect()->route('marketplace.cart')->withErrors([
                'cart' => 'Pilih TPS3R yang ingin diproses terlebih dahulu.',
            ]);
        }
        $cartData = $this->cartData($request, $sellerId);
        /** @var Collection<int, array{product: WasteProduct, jumlah: int, subtotal: float}> $items */
        $items = $cartData['items'];
        if ($items->isEmpty()) {
            return redirect()->route('marketplace.cart')->withErrors(['cart' => 'Keranjang belanja masih kosong.']);
        }

        /** @var User $buyer */
        $buyer = $request->user();
        $order = $createMarketplaceOrder->handle(
            $buyer,
            $request->orderData(array_values(
                $items->map(fn (array $item): array => [
                    'product_id' => $item['product']->id,
                    'jumlah' => $item['jumlah'],
                ])->all(),
            )),
        );

        /** @var array<string, int> $cart */
        $cart = $request->session()->get(self::CART_SESSION_KEY, []);
        foreach ($items as $item) {
            unset($cart[$item['product']->public_id]);
        }
        $request->session()->put(self::CART_SESSION_KEY, $cart);

        return redirect()->route('marketplace.orders.show', $order)
            ->with('success', 'Pesanan berhasil dibuat.');
    }

    public function orders(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $status = trim((string) $request->query('status', 'semua'));
        $allowedStatuses = ['semua', 'pending', 'diproses', 'siap_dikirim', 'dikirim', 'selesai', 'dibatalkan'];
        if (! in_array($status, $allowedStatuses, true)) {
            $status = 'semua';
        }

        $query = Order::query()
            ->where('buyer_id', $user->id)
            ->with(['items.product.seller.pengelolaTps3rProfile', 'fulfillments']);

        if ($status !== 'semua') {
            $status === 'diproses'
                ? $query->whereIn('status', ['dibayar', 'diproses'])
                : $query->where('status', $status);
        }

        return view('marketplace.orders', $this->viewData([
            'orders' => $query->latest()->paginate(10)->withQueryString(),
            'activeStatus' => $status,
        ]));
    }

    public function showOrder(Request $request, Order $order): View
    {
        $this->authorizeBuyer($request, $order);
        $order->load(['items.product.seller.pengelolaTps3rProfile', 'fulfillments.seller.pengelolaTps3rProfile']);

        return view('marketplace.order', $this->viewData(['order' => $order]));
    }

    public function cancelOrder(
        Request $request,
        Order $order,
        CancelMarketplaceOrder $cancelMarketplaceOrder,
    ): RedirectResponse {
        $this->authorizeBuyer($request, $order);
        $validated = $request->validate(['alasan' => ['required', 'string', 'min:5', 'max:300']]);
        $cancelMarketplaceOrder->handle($order, $validated['alasan']);

        return back()->with('success', 'Pesanan berhasil dibatalkan.');
    }

    public function confirmReceived(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeBuyer($request, $order);
        $order->load('fulfillments');

        if (
            $order->status !== 'dikirim'
            || $order->fulfillments->contains(fn ($fulfillment): bool => $fulfillment->status->value !== 'dikirim')
        ) {
            return back()->withErrors(['order' => 'Pesanan belum berstatus dikirim.']);
        }

        foreach ($order->fulfillments as $fulfillment) {
            $fulfillment->update([
                'status' => 'selesai',
                'completed_at' => now(),
                'catatan_status' => 'Pesanan diterima oleh pembeli.',
            ]);
        }
        $order->update(['status' => 'selesai']);
        $this->transactionNotifications->orderStatusChanged($order, 'dikirim');

        return back()->with('success', 'Pesanan dikonfirmasi telah diterima.');
    }

    /**
     * @return array{
     *     items: Collection<int, array{product: WasteProduct, jumlah: int, subtotal: float}>,
     *     groups: Collection<int, array{seller_id: int, seller_name: string, items: Collection<int, array{product: WasteProduct, jumlah: int, subtotal: float}>, subtotal: float}>,
     *     subtotal: float
     * }
     */
    private function cartData(Request $request, ?int $sellerId = null): array
    {
        /** @var array<string, int> $cart */
        $cart = $request->session()->get(self::CART_SESSION_KEY, []);
        $products = WasteProduct::query()
            ->whereIn('public_id', array_keys($cart))
            ->with('seller.pengelolaTps3rProfile')
            ->get()
            ->keyBy('public_id');

        $items = collect($cart)->map(function (int $quantity, string $publicId) use ($products): ?array {
            /** @var WasteProduct|null $product */
            $product = $products->get($publicId);
            if (! $product) {
                return null;
            }

            $quantity = min(max($quantity, 1), max($product->stok, 1));

            return [
                'product' => $product,
                'jumlah' => $quantity,
                'subtotal' => round($product->harga * $quantity, 2),
            ];
        })->filter()->values();

        if ($sellerId !== null) {
            $items = $items
                ->filter(fn (array $item): bool => (int) $item['product']->seller_id === $sellerId)
                ->values();
        }

        $groups = $items
            ->groupBy(fn (array $item): int => (int) $item['product']->seller_id)
            ->map(function (Collection $sellerItems, int $groupSellerId): array {
                /** @var array{product: WasteProduct, jumlah: int, subtotal: float} $firstItem */
                $firstItem = $sellerItems->first();
                $seller = $firstItem['product']->seller;

                return [
                    'seller_id' => $groupSellerId,
                    'seller_name' => $seller->pengelolaTps3rProfile->nama_tps3r ?? $seller->name,
                    'items' => $sellerItems->values(),
                    'subtotal' => (float) $sellerItems->sum('subtotal'),
                ];
            })
            ->values();

        return [
            'items' => $items,
            'groups' => $groups,
            'subtotal' => (float) $items->sum('subtotal'),
        ];
    }

    /**
     * @param  Collection<int, array{product: WasteProduct, jumlah: int, subtotal: float}>  $items
     */
    private function resolveCheckoutSellerId(Request $request, Collection $items): ?int
    {
        $sellerIds = $items
            ->map(fn (array $item): int => (int) $item['product']->seller_id)
            ->unique()
            ->values();
        $requestedSellerId = $request->integer('seller_id');

        if ($requestedSellerId > 0 && $sellerIds->contains($requestedSellerId)) {
            return $requestedSellerId;
        }

        return $sellerIds->count() === 1 ? (int) $sellerIds->first() : null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function viewData(array $data): array
    {
        return [...$data, 'portalSettings' => $this->portalData->homeOverview()['settings']];
    }

    private function oldString(Request $request, string $key, string $default = ''): string
    {
        $value = $request->old($key);

        return is_string($value) ? $value : $default;
    }

    private function authorizeBuyer(Request $request, Order $order): void
    {
        abort_unless($request->user()?->id === $order->buyer_id, 403);
    }
}
