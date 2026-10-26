<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MasterData;
use App\Models\Tps3rWasteDeposit;
use App\Models\User;
use App\Models\WasteProcessingRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class WasteProcessingController extends Controller
{
    /**
     * Daftar metode pengolahan baku TPS3R Kota Madiun.
     *
     * @return array<string, string>
     */
    public static function processMethods(): array
    {
        return [
            'composting' => 'Pengomposan (Kompos Organik)',
            'sorting' => 'Pemilahan Lanjutan',
            'shredding' => 'Pencacahan Plastik / Organik',
            'recycling' => 'Daur Ulang Anorganik',
            'residue_handling' => 'Penanganan Residu (TPA Winongo)',
        ];
    }

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $profile = $user->pengelolaTps3rProfile;
        abort_if($profile === null, Response::HTTP_UNPROCESSABLE_ENTITY, 'Lengkapi profil TPS3R terlebih dahulu.');

        // Ambil kategori sampah dari Data Master yang aktif
        $masterCategories = MasterData::query()
            ->where('group', 'waste_category')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'pillar']);

        return view('dashboard.tps3r.processing', [
            'records' => WasteProcessingRecord::query()
                ->where('pengelola_id', $user->id)
                ->with('deposit:id,nomor_resi,jenis_sampah,berat_kg')
                ->latest('started_on')
                ->latest('id')
                ->paginate(15),
            'deposits' => Tps3rWasteDeposit::query()
                ->where('pengelola_id', $user->id)
                ->latest('tanggal_setor')
                ->limit(50)
                ->get([
                    'id',
                    'nomor_resi',
                    'jenis_sampah',
                    'berat_kg',
                    'berat_organik_pilah_kg',
                    'berat_anorganik_pilah_kg',
                    'berat_residu_pilah_kg',
                ]),
            'masterCategories' => $masterCategories,
            'processMethods' => self::processMethods(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Ambil kode kategori dari Data Master agar validasi dinamis
        $masterCategoryCodes = MasterData::query()
            ->where('group', 'waste_category')
            ->where('is_active', true)
            ->pluck('code')
            ->all();

        $allowedWasteTypes = array_unique(array_merge(
            ['organik', 'anorganik_daur_ulang', 'residu'],
            $masterCategoryCodes
        ));

        $validated = $request->validate([
            'tps3r_waste_deposit_id' => ['nullable', 'integer', 'exists:tps3r_waste_deposits,id'],
            'process_method' => ['required', Rule::in(array_keys(self::processMethods()))],
            'waste_type' => ['required', Rule::in($allowedWasteTypes)],
            'input_weight_kg' => ['required', 'numeric', 'min:0.1', 'max:999999.99'],
            'started_on' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        /** @var User $user */
        $user = $request->user();
        $profile = $user->pengelolaTps3rProfile;
        abort_if($profile === null, Response::HTTP_UNPROCESSABLE_ENTITY);

        if (isset($validated['tps3r_waste_deposit_id'])) {
            Tps3rWasteDeposit::query()
                ->where('pengelola_id', $user->id)
                ->findOrFail($validated['tps3r_waste_deposit_id']);
        }

        WasteProcessingRecord::create([
            ...$validated,
            'pengelola_id' => $user->id,
            'tps3r_profile_id' => $profile->id,
            'status' => 'planned',
        ]);

        return back()->with('status', 'Batch pengolahan sampah berhasil dibuat.');
    }

    public function update(Request $request, WasteProcessingRecord $wasteProcessingRecord): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($wasteProcessingRecord->pengelola_id === $user->id, Response::HTTP_NOT_FOUND);
        $validated = $request->validate([
            'status' => ['required', Rule::in(['processing', 'completed', 'cancelled'])],
            'output_weight_kg' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $allowed = [
            'planned' => ['processing', 'cancelled'],
            'processing' => ['completed', 'cancelled'],
        ];

        if (! in_array($validated['status'], $allowed[$wasteProcessingRecord->status] ?? [], true)) {
            throw ValidationException::withMessages(['status' => ['Perubahan status pengolahan tidak valid.']]);
        }

        $wasteProcessingRecord->update([
            ...$validated,
            'completed_on' => $validated['status'] === 'completed' ? now()->toDateString() : null,
        ]);

        return back()->with('status', 'Status pengolahan berhasil diperbarui.');
    }
}
