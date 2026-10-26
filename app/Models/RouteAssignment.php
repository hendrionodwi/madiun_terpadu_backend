<?php

namespace App\Models;

use Database\Factories\RouteAssignmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'petugas_id',
    'assigned_by',
    'route_code',
    'route_name',
    'start_location',
    'end_facility',
    'district',
    'subdistrict',
    'schedule_day',
    'start_time',
    'end_time',
    'is_active',
    'notes',
    'latitude',
    'longitude',
    'waypoints',
])]
class RouteAssignment extends Model
{
    /** @use HasFactory<RouteAssignmentFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
            'waypoints' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    /** @return BelongsTo<User, $this> */
    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /** @return HasMany<RouteSession, $this> */
    public function routeSessions(): HasMany
    {
        return $this->hasMany(RouteSession::class, 'route_assignment_id');
    }

    /** @return HasOne<RouteSession, $this> */
    public function todaySession(): HasOne
    {
        return $this->hasOne(RouteSession::class, 'route_assignment_id')
            ->whereDate('tanggal', now()->toDateString());
    }
}
