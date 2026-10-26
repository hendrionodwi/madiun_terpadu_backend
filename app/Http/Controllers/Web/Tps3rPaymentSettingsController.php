<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PengelolaTps3rProfile;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class Tps3rPaymentSettingsController extends Controller
{
    /**
     * Show Midtrans payment configuration & interactive guide for TPS3R.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        // If Admin DLH is viewing, show either selected TPS3R or first TPS3R profile as reference
        if ($user->isAdminDlh()) {
            $tps3rUserId = $request->query('tps3r_user_id');
            $profile = $tps3rUserId
                ? PengelolaTps3rProfile::where('user_id', $tps3rUserId)->first()
                : PengelolaTps3rProfile::first();
        } else {
            $profile = PengelolaTps3rProfile::firstOrCreate(
                ['user_id' => $user->id],
                ['nama_tps3r' => $user->name],
            );
        }

        $allTps3rList = $user->isAdminDlh()
            ? PengelolaTps3rProfile::with('user:id,name,email')->get()
            : collect();

        $webhookUrl = url('/api/v1/marketplace/midtrans/notification');

        return view('dashboard.tps3r.payment-settings', [
            'profile' => $profile,
            'webhookUrl' => $webhookUrl,
            'isAdmin' => $user->isAdminDlh(),
            'allTps3rList' => $allTps3rList,
        ]);
    }

    /**
     * Update TPS3R's Midtrans credentials.
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'midtrans_merchant_id' => ['nullable', 'string', 'max:100'],
            'midtrans_client_key' => ['nullable', 'string', 'max:255'],
            'midtrans_server_key' => ['nullable', 'string', 'max:255'],
            'midtrans_is_production' => ['nullable', 'boolean'],
            'midtrans_is_active' => ['nullable', 'boolean'],
        ]);

        $profile = PengelolaTps3rProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['nama_tps3r' => $user->name],
        );

        $dataToUpdate = [
            'midtrans_merchant_id' => $validated['midtrans_merchant_id'] ?? null,
            'midtrans_client_key' => $validated['midtrans_client_key'] ?? null,
            'midtrans_is_production' => (bool) ($validated['midtrans_is_production'] ?? false),
            'midtrans_is_active' => (bool) ($validated['midtrans_is_active'] ?? false),
        ];

        // Only update server_key if filled (do not wipe if left blank on edit)
        if (! empty($validated['midtrans_server_key'])) {
            $dataToUpdate['midtrans_server_key'] = $validated['midtrans_server_key'];
        }

        $profile->update($dataToUpdate);

        return redirect()->route('dashboard.tps3r.payment-settings')
            ->with('status', 'Pengaturan gateway pembayaran Midtrans fasilitas TPS3R berhasil disimpan.');
    }

    /**
     * Test connection to Midtrans using provided or saved credentials.
     */
    public function test(Request $request, MidtransService $midtransService): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $profile = $user->pengelolaTps3rProfile;

        $serverKey = $request->input('server_key') ?: ($profile?->midtrans_server_key ?? '');
        $isProduction = $request->has('is_production')
            ? (bool) $request->input('is_production')
            : (bool) ($profile?->midtrans_is_production ?? false);

        if (empty($serverKey)) {
            $message = 'Server Key Midtrans belum diisi. Masukkan Server Key terlebih dahulu untuk melakukan pengujian koneksi.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return redirect()->back()->withErrors(['midtrans_server_key' => $message]);
        }

        $result = $midtransService->testConnection($serverKey, $isProduction);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return redirect()->back()->with('status', $result['message']);
        }

        return redirect()->back()->withErrors(['connection_test' => $result['message']]);
    }
}
