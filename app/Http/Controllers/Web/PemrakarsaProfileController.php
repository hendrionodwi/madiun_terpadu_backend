<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EntityVerification;
use App\Models\PemrakarsaProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PemrakarsaProfileController extends Controller
{
    public function edit(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $profile = PemrakarsaProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['nama_instansi' => $user->name],
        );

        return view('dashboard.pemrakarsa.profile', [
            'profile' => $profile,
            'verification' => EntityVerification::whereBelongsTo($user)->first(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_instansi' => ['required', 'string', 'max:255'],
            'jenis_instansi' => ['nullable', 'string', 'max:100'],
            'nib' => ['nullable', 'string', 'max:100'],
            'penanggung_jawab' => ['nullable', 'string', 'max:255'],
            'alamat_instansi' => ['nullable', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'legal_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $profile = PemrakarsaProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['nama_instansi' => $user->name],
        );
        $profile->update(collect($validated)->except('legal_document')->all());

        if ($request->filled('nib') || $request->hasFile('legal_document')) {
            $verification = EntityVerification::firstOrNew(['user_id' => $user->id]);
            $documentPath = $request->hasFile('legal_document')
                ? $request->file('legal_document')->store('entity-documents')
                : $verification->document_path;
            $verification->fill([
                'entity_type' => 'pemrakarsa',
                'document_number' => $validated['nib'] ?? $verification->document_number,
                'document_path' => $documentPath,
                'status' => 'submitted',
                'admin_note' => null,
                'reviewed_by' => null,
                'submitted_at' => now(),
                'reviewed_at' => null,
            ])->save();
        }

        return back()->with('status', 'Profil perusahaan dan legalitas berhasil diperbarui.');
    }
}
