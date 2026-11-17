<?php

namespace App\Models;

use Database\Factories\Tps3rDeliveryZoneFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $public_id
 * @property int $pengelola_tps3r_profile_id
 * @property string $kelurahan
 * @property string $kecamatan
 * @property float $delivery_fee
 * @property string $status
 * @property bool $is_active
 * @property int|null $approved_by
 * @property Carbon|null $approved_at
 * @property string|null $rejection_reason
 * @property-read PengelolaTps3rProfile $tps3rProfile
 * @property-read User|null $approver
 */
#[Fillable([
    'pengelola_tps3r_profile_id',
    'kelurahan',
    'kecamatan',
    'delivery_fee',
    'status',
    'is_active',
    'approved_by',
    'approved_at',
    'rejection_reason',
])]
class Tps3rDeliveryZone extends Model
{
    /** @use HasFactory<Tps3rDeliveryZoneFactory> */
    use HasFactory, HasUlids;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    /** @return list<string> */
    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'delivery_fee' => 'float',
            'is_active' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<PengelolaTps3rProfile, $this> */
    public function tps3rProfile(): BelongsTo
    {
        return $this->belongsTo(PengelolaTps3rProfile::class, 'pengelola_tps3r_profile_id');
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @param Builder<Tps3rDeliveryZone> $query */
    public function scopeApproved(Builder $query): void
    {
        $query->where('status', self::STATUS_APPROVED)->where('is_active', true);
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED && $this->is_active;
    }
}
