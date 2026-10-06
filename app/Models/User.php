<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property UserRole $role
 * @property string|null $phone_number
 * @property string|null $avatar
 * @property bool $is_active
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read WargaProfile|null $wargaProfile
 * @property-read PetugasLapanganProfile|null $petugasLapanganProfile
 * @property-read PengelolaTps3rProfile|null $pengelolaTps3rProfile
 * @property-read PemrakarsaProfile|null $pemrakarsaProfile
 * @property-read AdminDlhProfile|null $adminDlhProfile
 * @property-read Model|null $profile
 */
#[Fillable(['name', 'email', 'password', 'role', 'phone_number', 'avatar', 'is_active'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relation to Warga Profile.
     *
     * @return HasOne<WargaProfile, $this>
     */
    public function wargaProfile(): HasOne
    {
        return $this->hasOne(WargaProfile::class);
    }

    /**
     * Relation to Petugas Lapangan Profile.
     *
     * @return HasOne<PetugasLapanganProfile, $this>
     */
    public function petugasLapanganProfile(): HasOne
    {
        return $this->hasOne(PetugasLapanganProfile::class);
    }

    /**
     * Relation to Pengelola TPS3R Profile.
     *
     * @return HasOne<PengelolaTps3rProfile, $this>
     */
    public function pengelolaTps3rProfile(): HasOne
    {
        return $this->hasOne(PengelolaTps3rProfile::class);
    }

    /**
     * Relation to Pemrakarsa Profile.
     *
     * @return HasOne<PemrakarsaProfile, $this>
     */
    public function pemrakarsaProfile(): HasOne
    {
        return $this->hasOne(PemrakarsaProfile::class);
    }

    /**
     * Relation to Admin DLH Profile.
     *
     * @return HasOne<AdminDlhProfile, $this>
     */
    public function adminDlhProfile(): HasOne
    {
        return $this->hasOne(AdminDlhProfile::class);
    }

    /**
     * Get the active role-specific profile model.
     */
    public function getProfileAttribute(): ?Model
    {
        return match ($this->role) {
            UserRole::Warga => $this->wargaProfile,
            UserRole::PetugasLapangan => $this->petugasLapanganProfile,
            UserRole::PengelolaTps3r => $this->pengelolaTps3rProfile,
            UserRole::Pemrakarsa => $this->pemrakarsaProfile,
            UserRole::AdminDlh => $this->adminDlhProfile,
        };
    }

    /**
     * Check if user matches a specific role.
     */
    public function hasRole(UserRole|string $role): bool
    {
        $roleValue = $role instanceof UserRole ? $role->value : $role;

        return $this->role->value === $roleValue;
    }

    /**
     * Check if user is Warga.
     */
    public function isWarga(): bool
    {
        return $this->role === UserRole::Warga;
    }

    /**
     * Check if user is Petugas Lapangan.
     */
    public function isPetugasLapangan(): bool
    {
        return $this->role === UserRole::PetugasLapangan;
    }

    /**
     * Check if user is Pengelola TPS3R.
     */
    public function isPengelolaTps3r(): bool
    {
        return $this->role === UserRole::PengelolaTps3r;
    }

    /**
     * Check if user is Pemrakarsa.
     */
    public function isPemrakarsa(): bool
    {
        return $this->role === UserRole::Pemrakarsa;
    }

    /**
     * Check if user is Admin DLH.
     */
    public function isAdminDlh(): bool
    {
        return $this->role === UserRole::AdminDlh;
    }

    /**
     * Get the user's initials.
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }
}
