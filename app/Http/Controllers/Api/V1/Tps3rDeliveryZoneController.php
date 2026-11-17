<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DeliveryZone\StoreDeliveryZoneRequest;
use App\Http\Requests\Api\V1\DeliveryZone\UpdateDeliveryZoneRequest;
use App\Http\Resources\Tps3rDeliveryZoneResource;
use App\Models\PengelolaTps3rProfile;
use App\Models\Tps3rDeliveryZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class Tps3rDeliveryZoneController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $profile = $this->profile($request);
        $deliveryZones = $profile->deliveryZones()
            ->with('tps3rProfile')
            ->orderBy('kecamatan')
            ->orderBy('kelurahan')
            ->get();

        return Tps3rDeliveryZoneResource::collection($deliveryZones)->additional([
            'success' => true,
            'message' => 'Wilayah pengiriman TPS3R berhasil diambil.',
        ]);
    }

    public function publicIndex(PengelolaTps3rProfile $store): AnonymousResourceCollection
    {
        $deliveryZones = $store->deliveryZones()
            ->approved()
            ->with('tps3rProfile')
            ->orderBy('kecamatan')
            ->orderBy('kelurahan')
            ->get();

        return Tps3rDeliveryZoneResource::collection($deliveryZones)->additional([
            'success' => true,
            'message' => 'Wilayah pengiriman aktif berhasil diambil.',
        ]);
    }

    public function store(StoreDeliveryZoneRequest $request): JsonResponse
    {
        $profile = $this->profile($request);
        $deliveryZone = $profile->deliveryZones()->create([
            ...$request->validated(),
            'status' => Tps3rDeliveryZone::STATUS_PENDING,
            'is_active' => $request->boolean('is_active', true),
        ]);
        $deliveryZone->setRelation('tps3rProfile', $profile);

        return (new Tps3rDeliveryZoneResource($deliveryZone))
            ->additional([
                'success' => true,
                'message' => 'Wilayah pengiriman diajukan untuk ditinjau Admin DLH.',
            ])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(
        UpdateDeliveryZoneRequest $request,
        Tps3rDeliveryZone $tps3rDeliveryZone,
    ): Tps3rDeliveryZoneResource {
        $validated = $request->validated();
        $requiresReview = collect(['kelurahan', 'kecamatan', 'delivery_fee'])
            ->contains(fn (string $attribute): bool => array_key_exists($attribute, $validated)
                && (string) $tps3rDeliveryZone->getAttribute($attribute) !== (string) $validated[$attribute]);

        $tps3rDeliveryZone->fill($validated);

        if ($requiresReview) {
            $tps3rDeliveryZone->fill([
                'status' => Tps3rDeliveryZone::STATUS_PENDING,
                'approved_by' => null,
                'approved_at' => null,
                'rejection_reason' => null,
            ]);
        }

        $tps3rDeliveryZone->save();
        $tps3rDeliveryZone->load('tps3rProfile');

        return (new Tps3rDeliveryZoneResource($tps3rDeliveryZone))->additional([
            'success' => true,
            'message' => $requiresReview
                ? 'Perubahan wilayah diajukan kembali untuk ditinjau Admin DLH.'
                : 'Status layanan wilayah berhasil diperbarui.',
        ]);
    }

    public function destroy(
        Request $request,
        Tps3rDeliveryZone $tps3rDeliveryZone,
    ): JsonResponse {
        $profile = $this->profile($request);
        abort_unless(
            $tps3rDeliveryZone->pengelola_tps3r_profile_id === $profile->id,
            Response::HTTP_FORBIDDEN,
        );
        abort_if(
            $tps3rDeliveryZone->status === Tps3rDeliveryZone::STATUS_APPROVED,
            Response::HTTP_CONFLICT,
            'Wilayah yang telah disetujui harus dinonaktifkan, bukan dihapus.',
        );

        $tps3rDeliveryZone->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan wilayah pengiriman berhasil dihapus.',
        ]);
    }

    private function profile(Request $request): PengelolaTps3rProfile
    {
        $profile = $request->user()?->pengelolaTps3rProfile;
        abort_if(
            $profile === null,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Lengkapi profil TPS3R terlebih dahulu.',
        );

        return $profile;
    }
}
