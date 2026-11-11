<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        if ($user->isAdminDlh()) {
            return true;
        }

        if ($order->buyer_id === $user->id) {
            return true;
        }

        if ($user->isPengelolaTps3r()) {
            return $order->fulfillments()->where('seller_id', $user->id)->exists();
        }

        return false;
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->isAdminDlh() || $order->buyer_id === $user->id;
    }

    public function confirmReceived(User $user, Order $order): bool
    {
        return $user->isAdminDlh() || $order->buyer_id === $user->id;
    }
}
