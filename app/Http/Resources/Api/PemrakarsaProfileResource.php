<?php

namespace App\Http\Resources\Api;

use App\Models\PemrakarsaProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PemrakarsaProfile
 */
class PemrakarsaProfileResource extends JsonResource
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
            'nama_instansi' => $this->nama_instansi,
            'jenis_instansi' => $this->jenis_instansi,
            'nib' => $this->nib,
            'penanggung_jawab' => $this->penanggung_jawab,
            'alamat_instansi' => $this->alamat_instansi,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
