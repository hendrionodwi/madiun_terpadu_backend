<?php

namespace App\Http\Resources\Api;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role->value,
            'role_label' => $this->role->label(),
            'phone_number' => $this->phone_number,
            'avatar' => $this->avatar,
            'is_active' => $this->is_active,
            'profile' => $this->resolveProfileResource(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Resolve the corresponding profile resource based on the user's role.
     */
    protected function resolveProfileResource(): mixed
    {
        return match ($this->role) {
            UserRole::Warga => $this->wargaProfile ? new WargaProfileResource($this->wargaProfile) : null,
            UserRole::PetugasLapangan => $this->petugasLapanganProfile ? new PetugasLapanganProfileResource($this->petugasLapanganProfile) : null,
            UserRole::PengelolaTps3r => $this->pengelolaTps3rProfile ? new PengelolaTps3rProfileResource($this->pengelolaTps3rProfile) : null,
            UserRole::Pemrakarsa => $this->pemrakarsaProfile ? new PemrakarsaProfileResource($this->pemrakarsaProfile) : null,
            UserRole::AdminDlh => $this->adminDlhProfile ? new AdminDlhProfileResource($this->adminDlhProfile) : null,
        };
    }
}
