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
            'nama_tps3r' => $this->nama_tps3r,
            'kode_tps3r' => $this->kode_tps3r,
            'alamat' => $this->alamat,
            'kelurahan' => $this->kelurahan,
            'kecamatan' => $this->kecamatan,
            'kapasitas_harian_kg' => $this->kapasitas_harian_kg,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
