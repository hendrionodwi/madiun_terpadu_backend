<?php

namespace App\Models;

use Database\Factories\BroadcastAnnouncementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'created_by',
    'title',
    'summary',
    'content',
    'audience_role',
    'priority',
    'status',
    'kategori',
    'is_active',
    'show_on_portal',
    'published_at',
    'expires_at',
])]
class BroadcastAnnouncement extends Model
{
    /** @use HasFactory<BroadcastAnnouncementFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_on_portal' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Scope announcements that are currently active and published.
     *
     * @param  Builder<BroadcastAnnouncement>  $query
     * @return Builder<BroadcastAnnouncement>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('status')->orWhereIn('status', ['published', 'scheduled']))
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()));
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
