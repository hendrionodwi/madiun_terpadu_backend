<?php

namespace App\Http\Controllers\Web;

use App\Actions\Marketplace\CancelMarketplaceOrder;
use App\Actions\Marketplace\CreateMarketplaceOrder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Marketplace\StoreCheckoutRequest;
use App\Http\Requests\Web\Marketplace\UpdateCartRequest;
use App\Models\Order;
use App\Models\User;
use App\Models\WasteProduct;
use App\Services\PublicPortalDataService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class MarketplaceController extends Controller
{
    private const CART_SESSION_KEY = 'marketplace_cart';

    public function __construct(public PublicPortalDataService $portalData) {}

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

        $existingProducts = WasteProduct::query()
            ->whereIn('public_id', array_keys($cart))
            ->get(['public_id', 'seller_id']);

        if (
            $existingProducts->isNotEmpty()
            && $existingProducts->contains(
                fn (WasteProduct $existingProduct): bool => $existingProduct->seller_id !== $product->seller_id,
            )
        ) {
            throw ValidationException::withMessages([
                'cart' => 'Selesaikan pesanan dari fasilitas sebelumnya sebelum menambah produk toko lain.',
            ]);
        }

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
        $cartData = $this->cartData($request);
        if ($cartData['items']->isEmpty()) {
            return redirect()->route('marketplace.cart')->withErrors(['cart' => 'Keranjang belanja masih kosong.']);
        }

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

        return view('marketplace.checkout', $this->viewData([
            ...$cartData,
            'wargaProfile' => $user->wargaProfile,
            'deliveryZones' => $deliveryZones,
            'rajaongkirOriginId' => $tps3rProfile?->rajaongkir_origin_id,
            'cartWeight' => $cartWeight,
        ]));
    }

    public function storeCheckout(
        StoreCheckoutRequest $request,
        CreateMarketplaceOrder $createMarketplaceOrder,
    ): RedirectResponse {
        $cartData = $this->cartData($request);
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

        $request->session()->forget(self::CART_SESSION_KEY);

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

        return back()->with('success', 'Pesanan dikonfirmasi telah diterima.');
    }

    /** @return array{items: Collection<int, array{product: WasteProduct, jumlah: int, subtotal: float}>, subtotal: float} */
    private function cartData(Request $request): array
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

        return [
            'items' => $items,
            'subtotal' => (float) $items->sum('subtotal'),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function viewData(array $data): array
    {
        return [...$data, 'portalSettings' => $this->portalData->homeOverview()['settings']];
    }

    private function authorizeBuyer(Request $request, Order $order): void
    {
        abort_unless($request->user()?->id === $order->buyer_id, 403);
    }
}
