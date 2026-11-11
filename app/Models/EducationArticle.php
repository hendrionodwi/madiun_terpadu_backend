<?php

namespace App\Models;

use Database\Factories\EducationArticleFactory;
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
 * @property string|null $ringkasan
 * @property string $isi
 * @property string $penulis_nama
 * @property string|null $penulis_jabatan
 * @property int $waktu_baca_menit
 * @property string|null $cover_image
 * @property-read string|null $cover_url
 * @property string $status
 * @property bool $is_featured
 * @property Carbon|null $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $creator
 */
#[Fillable([
    'created_by',
    'judul',
    'slug',
    'kategori',
    'ringkasan',
    'isi',
    'penulis_nama',
    'penulis_jabatan',
    'waktu_baca_menit',
    'cover_image',
    'status',
    'is_featured',
    'published_at',
])]
class EducationArticle extends Model
{
    /** @use HasFactory<EducationArticleFactory> */
    use HasFactory, HasUlids;

    /** @var array<int, string> */
    protected $appends = [
        'cover_url',
        'kategori_label',
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
        static::creating(function (EducationArticle $article): void {
            if (empty($article->slug)) {
                $baseSlug = Str::slug($article->judul);
                $article->slug = $baseSlug.'-'.strtolower(Str::random(5));
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'waktu_baca_menit' => 'integer',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Scope articles that are published and live on the public portal.
     *
     * @param  Builder<EducationArticle>  $query
     * @return Builder<EducationArticle>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function getCoverUrlAttribute(): ?string
    {
        if (! $this->cover_image) {
            return null;
        }

        if (str_starts_with($this->cover_image, 'http://') || str_starts_with($this->cover_image, 'https://')) {
            return $this->cover_image;
        }

        return asset('storage/'.ltrim($this->cover_image, '/'));
    }

    public function getKategoriLabelAttribute(): string
    {
        return match ($this->kategori) {
            'organik' => 'Organik & Kompos',
            'plastik_kertas' => 'Plastik & Kertas',
            'b3_rumah_tangga' => 'B3 Rumah Tangga',
            'ekonomi_sirkular' => 'Ekonomi Sirkular',
            'kebijakan' => 'Kebijakan Kota',
            default => ucfirst(str_replace('_', ' ', $this->kategori)),
        };
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
