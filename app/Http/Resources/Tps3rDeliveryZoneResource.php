<?php

namespace App\Http\Resources;

use App\Models\Tps3rDeliveryZone;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Tps3rDeliveryZoneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Tps3rDeliveryZone $deliveryZone */
        $deliveryZone = $this->resource;
        $user = $request->user();
        $canViewReviewData = $user?->isAdminDlh() === true
            || $deliveryZone->tps3rProfile->user_id === $user?->id;

        return [
            'id' => $deliveryZone->public_id,
            'tps3r' => [
                'id' => $deliveryZone->tps3rProfile->public_id,
                'name' => $deliveryZone->tps3rProfile->nama_tps3r,
            ],
            'kelurahan' => $deliveryZone->kelurahan,
            'kecamatan' => $deliveryZone->kecamatan,
            'delivery_fee' => (int) round($deliveryZone->delivery_fee),
            'is_active' => $deliveryZone->is_active,
            'status' => $this->when($canViewReviewData, $deliveryZone->status),
            'rejection_reason' => $this->when($canViewReviewData, $deliveryZone->rejection_reason),
            'approved_at' => $this->when(
                $canViewReviewData && $deliveryZone->approved_at !== null,
                $deliveryZone->approved_at?->toIso8601String(),
            ),
            'created_at' => $deliveryZone->created_at?->toIso8601String(),
            'updated_at' => $deliveryZone->updated_at?->toIso8601String(),
        ];
    }
}
