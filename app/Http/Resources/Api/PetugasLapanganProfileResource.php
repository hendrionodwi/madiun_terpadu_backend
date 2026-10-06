<?php

namespace App\Http\Resources\Api;

use App\Models\PetugasLapanganProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PetugasLapanganProfile
 */
class PetugasLapanganProfileResource extends JsonResource
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
            'nip' => $this->nip,
            'id_wilayah_rute' => $this->id_wilayah_rute,
            'nama_wilayah_tugas' => $this->nama_wilayah_tugas,
            'jenis_kendaraan' => $this->jenis_kendaraan,
            'plat_nomor' => $this->plat_nomor,
            'status_tugas' => $this->status_tugas,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
