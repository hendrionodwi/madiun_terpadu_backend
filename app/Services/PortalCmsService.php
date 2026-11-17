<?php

namespace App\Services;

use App\Models\BroadcastAnnouncement;
use App\Models\EducationArticle;
use App\Models\PortalSetting;
use App\Models\PublicDocument;
use App\Models\User;
use Closure;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class PortalCmsService
{
    /**
     * Apply a wildcard-escaped LIKE search across one or more whitelisted columns.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @param  list<string>  $columns
     * @return Builder<TModel>
     */
    public static function applyEscapedLike(Builder $query, array $columns, ?string $search): Builder
    {
        $trimmed = trim((string) $search);
        if ($trimmed === '' || $columns === []) {
            return $query;
        }

        $escaped = strtr($trimmed, [
            '!' => '!!',
            '%' => '!%',
            '_' => '!_',
        ]);
        $pattern = '%'.$escaped.'%';

        return $query->where(function (Builder $sub) use ($columns, $pattern): void {
            foreach ($columns as $column) {
                $sub->orWhereRaw(self::escapedLikeSql($column), [$pattern]);
            }
        });
    }

    /**
     * Build dashboard summary counts and lists for the Admin Content Management hub.
     *
     * @return array<string, mixed>
     */
    public function adminSummary(): array
    {
        return [
            'active_announcements' => BroadcastAnnouncement::published()->count(),
            'scheduled_announcements' => BroadcastAnnouncement::where('status', 'scheduled')->count(),
            'total_articles' => EducationArticle::count(),
            'published_articles' => EducationArticle::published()->count(),
            'draft_articles' => EducationArticle::where('status', 'draft')->count(),
            'total_documents' => PublicDocument::count(),
            'published_documents' => PublicDocument::published()->count(),
        ];
    }

    /** @param array<string, mixed> $data */
    public function createAnnouncement(User $admin, array $data): BroadcastAnnouncement
    {
        $status = (string) ($data['status'] ?? 'published');
        $publishedAt = isset($data['published_at'])
            ? Carbon::parse((string) $data['published_at'])
            : ($status === 'published' ? now() : null);

        $isActive = in_array($status, ['archived', 'draft'], true)
            ? false
            : (isset($data['is_active'])
                ? (bool) $data['is_active']
                : true);

        return BroadcastAnnouncement::create([
            'created_by' => $admin->id,
            'title' => $data['title'],
            'summary' => $data['summary'] ?? null,
            'content' => $data['content'],
            'audience_role' => ($data['audience_role'] ?? 'all') === 'all' ? null : $data['audience_role'],
            'priority' => $data['priority'] ?? 'normal',
            'status' => $status,
            'kategori' => $data['kategori'] ?? 'informasi',
            'is_active' => $isActive,
            'show_on_portal' => (bool) ($data['show_on_portal'] ?? true),
            'published_at' => $publishedAt,
            'expires_at' => isset($data['expires_at'])
                ? Carbon::parse((string) $data['expires_at'])
                : null,
        ]);
    }

    /** @param array<string, mixed> $data */
    public function updateAnnouncement(BroadcastAnnouncement $announcement, array $data): BroadcastAnnouncement
    {
        $status = (string) ($data['status'] ?? $announcement->status);
        if (array_key_exists('is_active', $data) && $data['is_active'] !== null) {
            $isActive = (bool) $data['is_active'];
        } elseif (array_key_exists('status', $data)) {
            $isActive = ! in_array($status, ['archived', 'draft'], true);
        } else {
            $isActive = $announcement->is_active;
        }

        $audienceRole = array_key_exists('audience_role', $data)
            ? (($data['audience_role'] === 'all' || $data['audience_role'] === null) ? null : $data['audience_role'])
            : $announcement->audience_role;

        $announcement->update([
            'title' => $data['title'] ?? $announcement->title,
            'summary' => array_key_exists('summary', $data) ? $data['summary'] : $announcement->summary,
            'content' => $data['content'] ?? $announcement->content,
            'audience_role' => $audienceRole,
            'priority' => $data['priority'] ?? $announcement->priority,
            'status' => $status,
            'kategori' => $data['kategori'] ?? $announcement->kategori,
            'is_active' => $isActive,
            'show_on_portal' => array_key_exists('show_on_portal', $data)
                ? (bool) $data['show_on_portal']
                : $announcement->show_on_portal,
            'published_at' => array_key_exists('published_at', $data)
                ? ($data['published_at'] ? Carbon::parse((string) $data['published_at']) : null)
                : ($status === 'published' && $announcement->published_at === null ? now() : $announcement->published_at),
            'expires_at' => array_key_exists('expires_at', $data)
                ? ($data['expires_at'] ? Carbon::parse((string) $data['expires_at']) : null)
                : $announcement->expires_at,
        ]);

        return $announcement->fresh();
    }

    /** @param array<string, mixed> $data */
    public function createArticle(User $admin, array $data, ?UploadedFile $coverFile = null): EducationArticle
    {
        $coverPath = null;
        if ($coverFile !== null) {
            $coverPath = $this->storeUploadedFile($coverFile, 'articles', 'public');
        }

        $status = (string) ($data['status'] ?? 'published');
        $slug = ! empty($data['slug'])
            ? Str::slug((string) $data['slug'])
            : Str::slug((string) $data['judul']).'-'.strtolower(Str::random(5));

        return $this->persistWithUploadedFileCleanup($coverPath, fn (): EducationArticle => EducationArticle::create([
            'created_by' => $admin->id,
            'judul' => $data['judul'],
            'slug' => $slug,
            'kategori' => $data['kategori'],
            'ringkasan' => $data['ringkasan'] ?? null,
            'isi' => $data['isi'],
            'penulis_nama' => $data['penulis_nama'] ?? $admin->name,
            'penulis_jabatan' => $data['penulis_jabatan'] ?? 'Tim Penyuluh DLH Kota Madiun',
            'waktu_baca_menit' => (int) ($data['waktu_baca_menit'] ?? 4),
            'cover_image' => $coverPath,
            'status' => $status,
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'published_at' => isset($data['published_at'])
                ? Carbon::parse((string) $data['published_at'])
                : ($status === 'published' ? now() : null),
        ]));
    }

    /** @param array<string, mixed> $data */
    public function updateArticle(EducationArticle $article, array $data, ?UploadedFile $coverFile = null): EducationArticle
    {
        $oldCover = $article->cover_image;
        $newCover = $coverFile !== null
            ? $this->storeUploadedFile($coverFile, 'articles', 'public')
            : null;
        $coverPath = $newCover ?? $oldCover;
        $status = (string) ($data['status'] ?? $article->status);

        $updated = $this->persistWithUploadedFileCleanup($newCover, function () use ($article, $data, $coverPath, $status): EducationArticle {
            $article->update([
                'judul' => $data['judul'] ?? $article->judul,
                'kategori' => $data['kategori'] ?? $article->kategori,
                'ringkasan' => array_key_exists('ringkasan', $data) ? $data['ringkasan'] : $article->ringkasan,
                'isi' => $data['isi'] ?? $article->isi,
                'penulis_nama' => $data['penulis_nama'] ?? $article->penulis_nama,
                'penulis_jabatan' => array_key_exists('penulis_jabatan', $data) ? $data['penulis_jabatan'] : $article->penulis_jabatan,
                'waktu_baca_menit' => (int) ($data['waktu_baca_menit'] ?? $article->waktu_baca_menit),
                'cover_image' => $coverPath,
                'status' => $status,
                'is_featured' => array_key_exists('is_featured', $data)
                    ? (bool) $data['is_featured']
                    : $article->is_featured,
                'published_at' => array_key_exists('published_at', $data)
                    ? ($data['published_at'] ? Carbon::parse((string) $data['published_at']) : null)
                    : ($status === 'published' && $article->published_at === null ? now() : $article->published_at),
            ]);

            return $article->fresh();
        });

        if ($newCover !== null && $oldCover !== $newCover) {
            $this->deleteLocalPublicFile($oldCover);
        }

        return $updated;
    }

    public function deleteArticle(EducationArticle $article): void
    {
        $oldCover = $article->cover_image;

        DB::transaction(function () use ($article): void {
            $article->delete();
        });

        $this->deleteLocalPublicFile($oldCover);
    }

    /** @param array<string, mixed> $data */
    public function createDocument(User $admin, array $data, UploadedFile $file): PublicDocument
    {
        $path = $this->storeUploadedFile($file, 'documents', 'local');
        $slug = ! empty($data['slug'])
            ? Str::slug((string) $data['slug'])
            : Str::slug((string) $data['judul']).'-'.strtolower(Str::random(5));

        return $this->persistWithUploadedFileCleanup($path, fn (): PublicDocument => PublicDocument::create([
            'created_by' => $admin->id,
            'judul' => $data['judul'],
            'slug' => $slug,
            'kategori' => $data['kategori'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'file_path' => $path,
            'file_format' => strtoupper($file->getClientOriginalExtension() ?: 'PDF'),
            'file_size_bytes' => $file->getSize() ?: null,
            'tanggal_rilis' => $data['tanggal_rilis'] ?? now()->toDateString(),
            'hak_akses_label' => $data['hak_akses_label'] ?? 'Akses Bebas Publik',
            'status' => $data['status'] ?? 'published',
        ]), 'local');
    }

    /** @param array<string, mixed> $data */
    public function updateDocument(PublicDocument $document, array $data, ?UploadedFile $file = null): PublicDocument
    {
        $oldPath = $document->file_path;
        $newPath = $file !== null
            ? $this->storeUploadedFile($file, 'documents', 'local')
            : null;
        $path = $newPath ?? $oldPath;
        $format = $file !== null
            ? strtoupper($file->getClientOriginalExtension() ?: 'PDF')
            : $document->file_format;
        $size = $file !== null
            ? ($file->getSize() ?: $document->file_size_bytes)
            : $document->file_size_bytes;

        $updated = $this->persistWithUploadedFileCleanup($newPath, function () use ($document, $data, $path, $format, $size): PublicDocument {
            $document->update([
                'judul' => $data['judul'] ?? $document->judul,
                'kategori' => $data['kategori'] ?? $document->kategori,
                'deskripsi' => array_key_exists('deskripsi', $data) ? $data['deskripsi'] : $document->deskripsi,
                'file_path' => $path,
                'file_format' => $format,
                'file_size_bytes' => $size,
                'tanggal_rilis' => $data['tanggal_rilis'] ?? $document->tanggal_rilis,
                'hak_akses_label' => $data['hak_akses_label'] ?? $document->hak_akses_label,
                'status' => $data['status'] ?? $document->status,
            ]);

            return $document->fresh();
        }, 'local');

        if ($newPath !== null && $oldPath !== $newPath) {
            $this->deletePrivateDocumentFile($oldPath);
        }

        return $updated;
    }

    public function deleteDocument(PublicDocument $document): void
    {
        $oldPath = $document->file_path;

        DB::transaction(function () use ($document): void {
            $document->delete();
        });

        $this->deletePrivateDocumentFile($oldPath);
    }

    /**
     * Update portal configuration settings cleanly without overwriting unspecified keys.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function updateSettings(User $admin, array $payload): array
    {
        $defaults = PortalSetting::defaults();

        if (isset($payload['hero_mockup_image']) && $payload['hero_mockup_image'] instanceof UploadedFile) {
            $path = $this->storeUploadedFile($payload['hero_mockup_image'], 'portal-settings', 'public');
            $oldPath = PortalSetting::where('key', 'hero_mockup_image')->value('value');
            if ($oldPath) {
                $this->deleteLocalPublicFile($oldPath);
            }
            $payload['hero_mockup_image'] = $path;
        } elseif (! empty($payload['remove_hero_mockup_image'])) {
            $oldPath = PortalSetting::where('key', 'hero_mockup_image')->value('value');
            if ($oldPath) {
                $this->deleteLocalPublicFile($oldPath);
            }
            $payload['hero_mockup_image'] = null;
        }

        if (isset($payload['hero_background_image']) && $payload['hero_background_image'] instanceof UploadedFile) {
            $path = $this->storeUploadedFile($payload['hero_background_image'], 'portal-settings', 'public');
            $oldPath = PortalSetting::where('key', 'hero_background_image')->value('value');
            if ($oldPath) {
                $this->deleteLocalPublicFile($oldPath);
            }
            $payload['hero_background_image'] = $path;
        } elseif (! empty($payload['remove_hero_background_image'])) {
            $oldPath = PortalSetting::where('key', 'hero_background_image')->value('value');
            if ($oldPath) {
                $this->deleteLocalPublicFile($oldPath);
            }
            $payload['hero_background_image'] = null;
        }

        if (isset($payload['app_qr_custom_image']) && $payload['app_qr_custom_image'] instanceof UploadedFile) {
            $path = $this->storeUploadedFile($payload['app_qr_custom_image'], 'portal-settings', 'public');
            $oldPath = PortalSetting::where('key', 'app_qr_custom_image')->value('value');
            if ($oldPath) {
                $this->deleteLocalPublicFile($oldPath);
            }
            $payload['app_qr_custom_image'] = $path;
        } elseif (! empty($payload['remove_app_qr_custom_image'])) {
            $oldPath = PortalSetting::where('key', 'app_qr_custom_image')->value('value');
            if ($oldPath) {
                $this->deleteLocalPublicFile($oldPath);
            }
            $payload['app_qr_custom_image'] = null;
        }

        DB::transaction(function () use ($admin, $payload, $defaults): void {
            foreach ($payload as $key => $value) {
                if (! array_key_exists($key, $defaults)) {
                    continue;
                }

                $isJson = is_array($defaults[$key]);
                $isBool = in_array($key, [
                    'announcement_banner_enabled',
                    'show_banner_slider',
                    'show_stats_section',
                    'show_education_section',
                    'show_marketplace_section',
                    'show_app_download_section',
                ], true);

                $storedValue = match (true) {
                    $value === null => null,
                    $isJson && is_array($value) => json_encode($value, JSON_UNESCAPED_UNICODE),
                    $isBool => ((bool) $value) ? '1' : '0',
                    default => (string) $value,
                };

                PortalSetting::updateOrCreate(
                    ['key' => $key],
                    [
                        'group' => $this->resolveSettingGroup($key),
                        'label' => $this->resolveSettingLabel($key),
                        'type' => $isJson ? 'json' : ($isBool ? 'boolean' : 'text'),
                        'value' => $storedValue,
                        'updated_by' => $admin->id,
                    ],
                );
            }
        });

        return PortalSetting::resolved();
    }

    /**
     * Execute database persistence transactionally and remove any newly uploaded file on failure.
     *
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    private function persistWithUploadedFileCleanup(?string $uploadedPath, Closure $callback, string $disk = 'public'): mixed
    {
        try {
            return DB::transaction(static fn (Connection $connection): mixed => $callback());
        } catch (Throwable $exception) {
            $this->deleteStoredFile($disk, $uploadedPath);

            throw $exception;
        }
    }

    private function storeUploadedFile(UploadedFile $file, string $directory, string $disk): string
    {
        $path = $file->store($directory, $disk);
        if ($path === false) {
            throw new RuntimeException('Berkas portal gagal disimpan.');
        }

        return $path;
    }

    /** @return literal-string */
    private static function escapedLikeSql(string $column): string
    {
        return match ($column) {
            'title' => "title like ? escape '!'",
            'summary' => "summary like ? escape '!'",
            'content' => "content like ? escape '!'",
            'kategori_sampah_diterima' => "kategori_sampah_diterima like ? escape '!'",
            'nama_tps3r' => "nama_tps3r like ? escape '!'",
            'kode_tps3r' => "kode_tps3r like ? escape '!'",
            'alamat' => "alamat like ? escape '!'",
            'kelurahan' => "kelurahan like ? escape '!'",
            'kecamatan' => "kecamatan like ? escape '!'",
            'deskripsi' => "deskripsi like ? escape '!'",
            'subdistrict' => "subdistrict like ? escape '!'",
            'schedule_day' => "schedule_day like ? escape '!'",
            'route_code' => "route_code like ? escape '!'",
            'route_name' => "route_name like ? escape '!'",
            'district' => "district like ? escape '!'",
            'end_facility' => "end_facility like ? escape '!'",
            'judul' => "judul like ? escape '!'",
            'ringkasan' => "ringkasan like ? escape '!'",
            'isi' => "isi like ? escape '!'",
            'nama_produk' => "nama_produk like ? escape '!'",
            default => throw new InvalidArgumentException("Kolom pencarian {$column} tidak diizinkan."),
        };
    }

    private function deleteLocalPublicFile(?string $path): void
    {
        $this->deleteStoredFile('public', $path);
    }

    private function deletePrivateDocumentFile(?string $path): void
    {
        $this->deleteStoredFile('local', $path);
        $this->deleteStoredFile('public', $path);
    }

    private function deleteStoredFile(string $disk, ?string $path): void
    {
        if (! $path || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        Storage::disk($disk)->delete($path);
    }

    private function resolveSettingGroup(string $key): string
    {
        return match (true) {
            str_starts_with($key, 'hero_') || $key === 'operational_status_label' => 'hero',
            str_starts_with($key, 'contact_') => 'contact',
            str_starts_with($key, 'show_') || $key === 'announcement_banner_enabled' => 'sections',
            $key === 'service_steps' => 'workflow',
            default => 'transparency',
        };
    }

    private function resolveSettingLabel(string $key): string
    {
        return ucfirst(str_replace('_', ' ', $key));
    }
}
