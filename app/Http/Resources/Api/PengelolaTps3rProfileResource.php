<?php

namespace App\Http\Resources\Api;

use App\Models\PengelolaTps3rProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PengelolaTps3rProfile
 */
class PengelolaTps3rProfileResource extends JsonResource
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
            'public_id' => $this->public_id,
            'nama_tps3r' => $this->nama_tps3r,
            'kode_tps3r' => $this->kode_tps3r,
            'jenis_fasilitas' => $this->jenis_fasilitas,
            'deskripsi' => $this->deskripsi,
            'alamat' => $this->alamat,
            'kelurahan' => $this->kelurahan,
            'kecamatan' => $this->kecamatan,
            'jam_operasional' => $this->jam_operasional,
            'layanan_tersedia' => $this->layanan_tersedia ?? [],
            'kategori_sampah_diterima' => $this->kategori_sampah_diterima ?? [],
            'fokus_pengolahan' => $this->fokus_pengolahan,
            'cakupan_wilayah' => $this->cakupan_wilayah,
            'nama_koordinator' => $this->nama_koordinator,
            'kontak_publik' => $this->kontak_publik,
            'foto_url' => $this->foto_url,
            'kapasitas_harian_kg' => $this->kapasitas_harian_kg,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
