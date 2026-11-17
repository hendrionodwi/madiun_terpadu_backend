<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DeliveryZone\StoreDeliveryZoneRequest;
use App\Http\Requests\Api\V1\DeliveryZone\UpdateDeliveryZoneRequest;
use App\Models\PengelolaTps3rProfile;
use App\Models\Tps3rDeliveryZone;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class Tps3rDeliveryZoneController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $profile = PengelolaTps3rProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['nama_tps3r' => $user->name],
        );
        $deliveryZones = $profile->deliveryZones()
            ->with('approver:id,name')
            ->orderBy('kecamatan')
            ->orderBy('kelurahan')
            ->get();

        return view('dashboard.tps3r.delivery-zones', [
            'profile' => $profile,
            'deliveryZones' => $deliveryZones,
            'statusCounts' => [
                Tps3rDeliveryZone::STATUS_APPROVED => $deliveryZones->where('status', Tps3rDeliveryZone::STATUS_APPROVED)->count(),
                Tps3rDeliveryZone::STATUS_PENDING => $deliveryZones->where('status', Tps3rDeliveryZone::STATUS_PENDING)->count(),
                Tps3rDeliveryZone::STATUS_REJECTED => $deliveryZones->where('status', Tps3rDeliveryZone::STATUS_REJECTED)->count(),
            ],
        ]);
    }

    public function store(StoreDeliveryZoneRequest $request): RedirectResponse
    {
        $profile = $this->profile($request);
        $profile->deliveryZones()->create([
            ...$request->validated(),
            'status' => Tps3rDeliveryZone::STATUS_PENDING,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('status', 'Wilayah pengiriman diajukan ke Admin DLH.');
    }

    public function update(
        UpdateDeliveryZoneRequest $request,
        Tps3rDeliveryZone $tps3rDeliveryZone,
    ): RedirectResponse {
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

        return back()->with(
            'status',
            $requiresReview
                ? 'Perubahan diajukan kembali ke Admin DLH.'
                : 'Status layanan wilayah diperbarui.',
        );
    }

    public function destroy(Request $request, Tps3rDeliveryZone $tps3rDeliveryZone): RedirectResponse
    {
        $profile = $this->profile($request);
        abort_unless(
            $tps3rDeliveryZone->pengelola_tps3r_profile_id === $profile->id,
            Response::HTTP_FORBIDDEN,
        );

        if ($tps3rDeliveryZone->status === Tps3rDeliveryZone::STATUS_APPROVED) {
            return back()->withErrors([
                'delivery_zone' => 'Wilayah yang telah disetujui harus dinonaktifkan, bukan dihapus.',
            ]);
        }

        $tps3rDeliveryZone->delete();

        return back()->with('status', 'Pengajuan wilayah dihapus.');
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
