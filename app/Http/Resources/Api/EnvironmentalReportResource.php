<?php

namespace App\Http\Resources\Api;

use App\Models\EnvironmentalReport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin EnvironmentalReport */
class EnvironmentalReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pemrakarsa_id' => $this->pemrakarsa_id,
            'pemrakarsa' => $this->whenLoaded('pemrakarsa', fn () => [
                'id' => $this->pemrakarsa->id,
                'name' => $this->pemrakarsa->name,
                'email' => $this->pemrakarsa->email,
            ]),
            'judul_laporan' => $this->judul_laporan,
            'periode_semester' => $this->periode_semester,
            'tahun' => $this->tahun,
            'jenis_dokumen' => $this->jenis_dokumen,
            'timbulan_sampah_kg' => $this->timbulan_sampah_kg,
            'metode_pengelolaan' => $this->metode_pengelolaan,
            'file_path' => $this->file_path,
            'file_url' => Storage::disk('public')->url($this->file_path),
            'status' => $this->status,
            'catatan_verifikasi' => $this->catatan_verifikasi,
            'assigned_petugas_id' => $this->assigned_petugas_id,
            'assigned_petugas' => $this->whenLoaded('assignedPetugas', fn () => [
                'id' => $this->assignedPetugas->id,
                'name' => $this->assignedPetugas->name,
                'phone_number' => $this->assignedPetugas->phone_number,
            ]),
            'instruksi_lapangan' => $this->instruksi_lapangan,
            'catatan_inspeksi_petugas' => $this->catatan_inspeksi_petugas,
            'hasil_inspeksi_lapangan' => $this->hasil_inspeksi_lapangan,
            'assigned_at' => $this->assigned_at?->toISOString(),
            'inspeksi_at' => $this->inspeksi_at?->toISOString(),
            'verified_at' => $this->verified_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
