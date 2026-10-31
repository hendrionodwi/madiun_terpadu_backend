<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Marketplace\TransitionOrderFulfillmentStatus;
use App\Enums\OrderFulfillmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Marketplace\UpdateOrderFulfillmentRequest;
use App\Models\OrderFulfillment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class OrderFulfillmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::enum(OrderFulfillmentStatus::class)],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        /** @var User $seller */
        $seller = $request->user();

        $fulfillments = $this->sellerFulfillmentsQuery($seller)
            ->when(
                isset($validated['status']),
                fn (Builder $query) => $query->where('status', $validated['status']),
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate((int) ($validated['per_page'] ?? 10));

        return response()->json([
            'success' => true,
            'message' => 'Daftar pemrosesan pesanan TPS3R berhasil diambil.',
            'data' => $fulfillments->getCollection()
                ->map(fn (OrderFulfillment $fulfillment): array => $this->payload($fulfillment))
                ->values(),
            'meta' => [
                'current_page' => $fulfillments->currentPage(),
                'last_page' => $fulfillments->lastPage(),
                'per_page' => $fulfillments->perPage(),
                'total' => $fulfillments->total(),
            ],
        ], Response::HTTP_OK);
    }

    public function show(Request $request, OrderFulfillment $orderFulfillment): JsonResponse
    {
        /** @var User $seller */
        $seller = $request->user();
        $fulfillment = $this->sellerFulfillmentsQuery($seller)
            ->whereKey($orderFulfillment->getKey())
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'message' => 'Detail pemrosesan pesanan TPS3R berhasil diambil.',
            'data' => $this->payload($fulfillment),
        ], Response::HTTP_OK);
    }

    public function update(
        UpdateOrderFulfillmentRequest $request,
        OrderFulfillment $orderFulfillment,
        TransitionOrderFulfillmentStatus $transition,
    ): JsonResponse {
        /** @var User $seller */
        $seller = $request->user();
        $fulfillment = $this->sellerFulfillmentsQuery($seller)
            ->whereKey($orderFulfillment->getKey())
            ->firstOrFail();
        $validated = $request->validated();

        $fulfillment = $transition->handle(
            $fulfillment,
            OrderFulfillmentStatus::from($validated['status']),
            $validated['catatan_status'] ?? null,
        );
        $fulfillment = $this->sellerFulfillmentsQuery($seller)->findOrFail($fulfillment->id);

        return response()->json([
            'success' => true,
            'message' => 'Status pemrosesan pesanan berhasil diperbarui.',
            'data' => $this->payload($fulfillment),
        ], Response::HTTP_OK);
    }

    /** @return Builder<OrderFulfillment> */
    private function sellerFulfillmentsQuery(User $seller): Builder
    {
        return OrderFulfillment::query()
            ->whereBelongsTo($seller, 'seller')
            ->with([
                'order.buyer:id,name,phone_number',
                'order.items' => fn ($query) => $query
                    ->whereHas('product', fn ($productQuery) => $productQuery->where('seller_id', $seller->id))
                    ->with('product:id,seller_id,nama_produk'),
            ]);
    }

    /** @return array<string, mixed> */
    private function payload(OrderFulfillment $fulfillment): array
    {
        $order = $fulfillment->order;

        return [
            'id' => $fulfillment->id,
            'public_id' => $fulfillment->public_id,
            'order_id' => $order->id,
            'nomor_pesanan' => $order->nomor_pesanan,
            'status' => $fulfillment->status->value,
            'status_label' => $fulfillment->status->label(),
            'available_transitions' => array_map(
                fn (OrderFulfillmentStatus $status): string => $status->value,
                $fulfillment->status->allowedTransitions(),
            ),
            'catatan_status' => $fulfillment->catatan_status,
            'subtotal' => (float) $order->items->sum('subtotal'),
            'metode_pembayaran' => $order->metode_pembayaran,
            'alamat_pengiriman' => $order->alamat_pengiriman,
            'buyer' => [
                'id' => $order->buyer->id,
                'name' => $order->buyer->name,
                'phone_number' => $order->buyer->phone_number,
            ],
            'items' => $order->items->map(fn ($item): array => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'nama_produk' => $item->product->nama_produk,
                'jumlah' => $item->jumlah,
                'harga_satuan' => $item->harga_satuan,
                'subtotal' => $item->subtotal,
            ])->values(),
            'processed_at' => $fulfillment->processed_at?->toISOString(),
            'ready_at' => $fulfillment->ready_at?->toISOString(),
            'completed_at' => $fulfillment->completed_at?->toISOString(),
            'cancelled_at' => $fulfillment->cancelled_at?->toISOString(),
            'created_at' => $order->created_at?->toISOString(),
        ];
    }
}
