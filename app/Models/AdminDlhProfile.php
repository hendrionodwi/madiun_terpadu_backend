<?php

namespace App\Models;

use Database\Factories\AdminDlhProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $nip
 * @property string|null $jabatan
 * @property string $bidang
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 */
#[Fillable([
    'user_id',
    'nip',
    'jabatan',
    'bidang',
])]
class AdminDlhProfile extends Model
{
    /** @use HasFactory<AdminDlhProfileFactory> */
    use HasFactory;

    /**
     * Relation to User.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
