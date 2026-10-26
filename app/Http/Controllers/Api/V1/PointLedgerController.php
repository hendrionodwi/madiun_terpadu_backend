<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PointLedger;
use App\Models\User;
use App\Models\WargaProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PointLedgerController extends Controller
{
    /**
     * Get citizen's chronological point transaction ledger (TRK-F-010).
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();

        $query = PointLedger::query();

        if ($currentUser->isWarga()) {
            $query->where('warga_id', $currentUser->id);
        } elseif ($currentUser->isAdminDlh()) {
            if ($request->filled('warga_id')) {
                $query->where('warga_id', (int) $request->warga_id);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Akses mutasi poin hanya untuk warga dan Admin DLH.',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($request->filled('tipe') && in_array($request->tipe, ['kredit', 'debit'], true)) {
            $query->where('tipe', $request->tipe);
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $wargaId = $currentUser->isWarga()
            ? $currentUser->id
            : ($request->filled('warga_id') ? (int) $request->warga_id : null);
        $summaryQuery = PointLedger::query();

        if ($wargaId !== null) {
            $summaryQuery->where('warga_id', $wargaId);
        }

        $saldoSaatIni = $wargaId === null
            ? null
            : (int) (WargaProfile::query()->where('user_id', $wargaId)->value('saldo_poin') ?? 0);
        $totalKredit = (int) (clone $summaryQuery)->where('tipe', 'kredit')->sum('jumlah_poin');
        $totalDebit = (int) (clone $summaryQuery)->where('tipe', 'debit')->sum('jumlah_poin');
        $perPage = min(100, max(1, (int) ($request->per_page ?? 20)));
        $mutations = $query->latest('id')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Riwayat mutasi buku kas poin warga berhasil diambil.',
            'data' => [
                'saldo_saat_ini' => $saldoSaatIni,
                'total_kredit' => $totalKredit,
                'total_debit' => $totalDebit,
                'mutations' => $mutations->items(),
            ],
            'meta' => [
                'current_page' => $mutations->currentPage(),
                'last_page' => $mutations->lastPage(),
                'per_page' => $mutations->perPage(),
                'total' => $mutations->total(),
            ],
        ], Response::HTTP_OK);
    }
}
