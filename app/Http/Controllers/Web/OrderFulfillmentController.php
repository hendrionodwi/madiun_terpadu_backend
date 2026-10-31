<?php

namespace App\Http\Controllers\Web;

use App\Actions\Marketplace\TransitionOrderFulfillmentStatus;
use App\Enums\OrderFulfillmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Marketplace\UpdateOrderFulfillmentRequest;
use App\Models\OrderFulfillment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class OrderFulfillmentController extends Controller
{
    public function update(
        UpdateOrderFulfillmentRequest $request,
        OrderFulfillment $orderFulfillment,
        TransitionOrderFulfillmentStatus $transition,
    ): RedirectResponse {
        /** @var User $seller */
        $seller = $request->user();
        $fulfillment = OrderFulfillment::query()
            ->with('order')
            ->whereBelongsTo($seller, 'seller')
            ->whereKey($orderFulfillment->getKey())
            ->firstOrFail();
        $validated = $request->validated();

        $transition->handle(
            $fulfillment,
            OrderFulfillmentStatus::from($validated['status']),
            $validated['catatan_status'] ?? null,
        );

        return to_route('dashboard.tps3r.orders.show', $fulfillment->order)
            ->with('status', 'Status pemrosesan pesanan berhasil diperbarui.');
    }
}
