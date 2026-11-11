<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WasteProduct;

class WasteProductPolicy
{
    public function create(User $user): bool
    {
        return $user->isAdminDlh() || $user->isPengelolaTps3r() || $user->isPemrakarsa();
    }

    public function update(User $user, WasteProduct $wasteProduct): bool
    {
        return $user->isAdminDlh() || $wasteProduct->seller_id === $user->id;
    }

    public function delete(User $user, WasteProduct $wasteProduct): bool
    {
        return $user->isAdminDlh() || $wasteProduct->seller_id === $user->id;
    }
}
