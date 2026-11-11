<?php

namespace App\Models;

use Database\Factories\PublicDocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $public_id
 * @property int $created_by
 * @property string $judul
 * @property string $slug
 * @property string $kategori
 * @property string|null $deskripsi
 * @property string $file_path
 * @property string $file_format
 * @property int|null $file_size_bytes
 * @property Carbon|null $tanggal_rilis
 * @property string $hak_akses_label
 * @property string $status
 * @property int $download_count
 * @property-read string $formatted_size
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $creator
 */
#[Fillable([
    'created_by',
    'judul',
    'slug',
    'kategori',
    'deskripsi',
    'file_path',
    'file_format',
    'file_size_bytes',
    'tanggal_rilis',
    'hak_akses_label',
    'status',
    'download_count',
])]
class PublicDocument extends Model
{
    /** @use HasFactory<PublicDocumentFactory> */
    use HasFactory, HasUlids;

    /** @var array<int, string> */
    protected $appends = [
        'formatted_size',
        'kategori_label',
    ];

    /** @var list<string> */
    protected $hidden = [
        'file_path',
    ];

    /** @return list<string> */
    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        if ($field !== null) {
            return parent::resolveRouteBinding($value, $field);
        }

        if (is_numeric($value)) {
            return null;
        }

        return $this->where('slug', $value)
            ->orWhere('public_id', $value)
            ->first();
    }

    protected static function booted(): void
    {
        static::creating(function (PublicDocument $document): void {
            if (empty($document->slug)) {
                $baseSlug = Str::slug($document->judul);
                $document->slug = $baseSlug.'-'.strtolower(Str::random(5));
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size_bytes' => 'integer',
            'tanggal_rilis' => 'date',
            'download_count' => 'integer',
        ];
    }

    /**
     * Scope documents published for public access.
     *
     * @param  Builder<PublicDocument>  $query
     * @return Builder<PublicDocument>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size_bytes ?? 0;

        if ($bytes <= 0) {
            return 'PDF';
        }

        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 0, ',', '.').' KB';
        }

        return number_format($bytes / (1024 * 1024), 1, ',', '.').' MB';
    }

    public function getKategoriLabelAttribute(): string
    {
        return match ($this->kategori) {
            'panduan' => 'Panduan Praktis',
            'regulasi' => 'Regulasi & Perda',
            'peta' => 'Peta & Direktori',
            'laporan' => 'Laporan Kinerja',
            'sop' => 'SOP Operasional',
            default => ucfirst(str_replace('_', ' ', $this->kategori)),
        };
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
