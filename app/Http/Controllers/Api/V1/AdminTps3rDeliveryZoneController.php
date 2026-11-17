<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DeliveryZone\ReviewDeliveryZoneRequest;
use App\Http\Resources\Tps3rDeliveryZoneResource;
use App\Models\Tps3rDeliveryZone;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class AdminTps3rDeliveryZoneController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $deliveryZones = Tps3rDeliveryZone::query()
            ->with('tps3rProfile')
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', (string) $request->query('status')),
            )
            ->when(
                $request->filled('kecamatan'),
                fn ($query) => $query->where('kecamatan', (string) $request->query('kecamatan')),
            )
            ->latest()
            ->paginate(min(50, max(1, $request->integer('per_page', 15))));

        return Tps3rDeliveryZoneResource::collection($deliveryZones)->additional([
            'success' => true,
            'message' => 'Daftar pengajuan wilayah pengiriman berhasil diambil.',
        ]);
    }

    public function review(
        ReviewDeliveryZoneRequest $request,
        Tps3rDeliveryZone $tps3rDeliveryZone,
    ): Tps3rDeliveryZoneResource {
        $validated = $request->validated();

        $deliveryZone = DB::transaction(function () use ($request, $tps3rDeliveryZone, $validated): Tps3rDeliveryZone {
            $lockedZone = Tps3rDeliveryZone::query()
                ->whereKey($tps3rDeliveryZone->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $isApproved = $validated['status'] === Tps3rDeliveryZone::STATUS_APPROVED;

            $lockedZone->update([
                'status' => $validated['status'],
                'is_active' => $isApproved,
                'approved_by' => $request->user()->id,
                'approved_at' => $isApproved ? now() : null,
                'rejection_reason' => $isApproved ? null : $validated['rejection_reason'],
            ]);

            return $lockedZone;
        });
        $deliveryZone->load('tps3rProfile');

        return (new Tps3rDeliveryZoneResource($deliveryZone))->additional([
            'success' => true,
            'message' => $deliveryZone->status === Tps3rDeliveryZone::STATUS_APPROVED
                ? 'Wilayah pengiriman disetujui dan telah aktif.'
                : 'Pengajuan wilayah pengiriman ditolak.',
        ]);
    }
}
