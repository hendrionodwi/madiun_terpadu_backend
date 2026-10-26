<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EntityVerification;
use App\Models\MasterData;
use App\Models\PengelolaTps3rProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class Tps3rProfileController extends Controller
{
    public function edit(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $profile = PengelolaTps3rProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['nama_tps3r' => $user->name],
        );

        $districts = MasterData::where('group', 'districts')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name')
            ->all();

        return view('dashboard.tps3r.profile', [
            'profile' => $profile,
            'verification' => EntityVerification::whereBelongsTo($user)->first(),
            'districts' => $districts,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_tps3r' => ['required', 'string', 'max:255'],
            'kode_tps3r' => ['nullable', 'string', 'max:50'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'kelurahan' => ['nullable', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'kapasitas_harian_kg' => ['nullable', 'numeric', 'min:1', 'max:999999.99'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'document_number' => ['nullable', 'string', 'max:100'],
            'legal_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $profile = PengelolaTps3rProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['nama_tps3r' => $user->name],
        );
        $profile->update(collect($validated)->only([
            'nama_tps3r', 'kode_tps3r', 'alamat', 'kelurahan', 'kecamatan',
            'kapasitas_harian_kg', 'latitude', 'longitude',
        ])->all());

        if ($request->filled('document_number') || $request->hasFile('legal_document')) {
            $verification = EntityVerification::firstOrNew(['user_id' => $user->id]);
            $documentPath = $request->hasFile('legal_document')
                ? $request->file('legal_document')->store('entity-documents')
                : $verification->document_path;
            $verification->fill([
                'entity_type' => 'tps3r',
                'document_number' => $validated['document_number'] ?? $verification->document_number,
                'document_path' => $documentPath,
                'status' => 'submitted',
                'admin_note' => null,
                'reviewed_by' => null,
                'submitted_at' => now(),
                'reviewed_at' => null,
            ])->save();
        }

        return back()->with('status', 'Profil TPS3R dan kapasitas berhasil diperbarui.');
    }
}
