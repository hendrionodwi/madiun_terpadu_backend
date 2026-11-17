<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DeliveryZone\ReviewDeliveryZoneRequest;
use App\Models\Tps3rDeliveryZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminTps3rDeliveryZoneController extends Controller
{
    public function index(Request $request): View
    {
        $allowedStatuses = [
            Tps3rDeliveryZone::STATUS_PENDING,
            Tps3rDeliveryZone::STATUS_APPROVED,
            Tps3rDeliveryZone::STATUS_REJECTED,
        ];
        $allowedDistricts = ['Kartoharjo', 'Manguharjo', 'Taman'];
        $status = in_array($request->query('status'), $allowedStatuses, true)
            ? (string) $request->query('status')
            : null;
        $district = in_array($request->query('kecamatan'), $allowedDistricts, true)
            ? (string) $request->query('kecamatan')
            : null;
        $search = trim((string) $request->query('q'));

        $deliveryZones = Tps3rDeliveryZone::query()
            ->with(['tps3rProfile.user:id,name,email', 'approver:id,name'])
            ->when($status, fn (Builder $query, string $value): Builder => $query->where('status', $value))
            ->when($district, fn (Builder $query, string $value): Builder => $query->where('kecamatan', $value))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $nestedQuery) use ($search): void {
                    $nestedQuery
                        ->where('kelurahan', 'like', "%{$search}%")
                        ->orWhere('kecamatan', 'like', "%{$search}%")
                        ->orWhereHas(
                            'tps3rProfile',
                            fn (Builder $profileQuery): Builder => $profileQuery->where('nama_tps3r', 'like', "%{$search}%"),
                        );
                });
            })
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 WHEN status = 'rejected' THEN 1 ELSE 2 END")
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('dashboard.admin.tps3r-delivery-zones', [
            'deliveryZones' => $deliveryZones,
            'statusCounts' => Tps3rDeliveryZone::query()
                ->selectRaw('status, COUNT(*) as aggregate')
                ->groupBy('status')
                ->pluck('aggregate', 'status'),
            'filters' => [
                'status' => $status,
                'kecamatan' => $district,
                'q' => $search,
            ],
        ]);
    }

    public function review(
        ReviewDeliveryZoneRequest $request,
        Tps3rDeliveryZone $tps3rDeliveryZone,
    ): RedirectResponse {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $tps3rDeliveryZone, $validated): void {
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
        });

        return back()->with(
            'status',
            $validated['status'] === Tps3rDeliveryZone::STATUS_APPROVED
                ? 'Wilayah pengiriman disetujui dan diaktifkan.'
                : 'Pengajuan wilayah ditolak.',
        );
    }
}
