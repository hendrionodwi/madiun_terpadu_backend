<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $route_assignment_id
 * @property int $petugas_id
 * @property string $route_code
 * @property Carbon $tanggal
 * @property string $status
 * @property string|null $waktu_mulai
 * @property string|null $waktu_selesai
 * @property string|null $start_location
 * @property string|null $end_facility
 * @property float|null $start_latitude
 * @property float|null $start_longitude
 * @property float|null $end_latitude
 * @property float|null $end_longitude
 * @property string|null $catatan
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read RouteAssignment $routeAssignment
 * @property-read User $petugas
 */
#[Fillable([
    'route_assignment_id',
    'petugas_id',
    'route_code',
    'tanggal',
    'status',
    'waktu_mulai',
    'waktu_selesai',
    'start_location',
    'end_facility',
    'start_latitude',
    'start_longitude',
    'end_latitude',
    'end_longitude',
    'catatan',
])]
class RouteSession extends Model
{
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'start_latitude' => 'float',
            'start_longitude' => 'float',
            'end_latitude' => 'float',
            'end_longitude' => 'float',
        ];
    }

    /** @return BelongsTo<RouteAssignment, $this> */
    public function routeAssignment(): BelongsTo
    {
        return $this->belongsTo(RouteAssignment::class, 'route_assignment_id');
    }

    /** @return BelongsTo<User, $this> */
    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    /**
     * Scope query untuk sesi hari ini.
     *
     * @param  Builder<RouteSession>  $query
     * @return Builder<RouteSession>
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('tanggal', now()->toDateString());
    }

    public function isTerjadwal(): bool
    {
        return $this->status === 'terjadwal';
    }

    public function isBerlangsung(): bool
    {
        return $this->status === 'berlangsung';
    }

    public function isSelesai(): bool
    {
        return $this->status === 'selesai';
    }
}
