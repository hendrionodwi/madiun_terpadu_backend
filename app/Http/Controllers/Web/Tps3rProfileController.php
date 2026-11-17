<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EntityVerification;
use App\Models\MasterData;
use App\Models\PengelolaTps3rProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'deskripsi' => ['nullable', 'string', 'max:1500'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'kelurahan' => ['nullable', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'kapasitas_harian_kg' => ['nullable', 'numeric', 'min:1', 'max:999999.99'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'jam_operasional' => ['nullable', 'string', 'max:255'],
            'fokus_pengolahan' => ['nullable', 'string', 'max:255'],
            'cakupan_wilayah' => ['nullable', 'string', 'max:255'],
            'nama_koordinator' => ['nullable', 'string', 'max:255'],
            'kontak_publik' => ['nullable', 'string', 'max:100'],
            'layanan_tersedia_text' => ['nullable', 'string', 'max:1000'],
            'kategori_sampah_diterima_text' => ['nullable', 'string', 'max:1000'],
            'foto_fasilitas' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'document_number' => ['nullable', 'string', 'max:100'],
            'legal_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $profile = PengelolaTps3rProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['nama_tps3r' => $user->name],
        );
        $profileFields = [
            'nama_tps3r', 'kode_tps3r', 'deskripsi', 'alamat', 'kelurahan', 'kecamatan',
            'kapasitas_harian_kg', 'latitude', 'longitude', 'jam_operasional',
            'fokus_pengolahan', 'cakupan_wilayah', 'nama_koordinator', 'kontak_publik',
        ];
        $profileData = array_intersect_key($validated, array_flip($profileFields));
        $profileData['layanan_tersedia'] = $this->parsePublicList($validated['layanan_tersedia_text'] ?? null);
        $profileData['kategori_sampah_diterima'] = $this->parsePublicList($validated['kategori_sampah_diterima_text'] ?? null);

        if ($request->hasFile('foto_fasilitas')) {
            if ($profile->foto_fasilitas && ! str_starts_with($profile->foto_fasilitas, 'http')) {
                Storage::disk('public')->delete($profile->foto_fasilitas);
            }

            $profileData['foto_fasilitas'] = $request->file('foto_fasilitas')->store('tps3r/facilities', 'public');
        }

        $profile->update($profileData);

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

    /** @return list<string> */
    private function parsePublicList(?string $value): array
    {
        return array_values(collect(preg_split('/[\r\n,]+/', (string) $value) ?: [])
            ->map(fn (string $item): string => trim($item))
            ->filter()
            ->unique(fn (string $item): string => mb_strtolower($item))
            ->take(12)
            ->values()
            ->all());
    }
}
