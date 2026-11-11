<?php

namespace App\Http\Resources\Api;

use App\Models\EducationArticle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EducationArticle */
class EducationArticleResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'judul' => $this->judul,
            'slug' => $this->slug,
            'kategori' => $this->kategori,
            'kategori_label' => $this->kategori_label,
            'ringkasan' => $this->ringkasan,
            'isi' => $this->isi,
            'penulis_nama' => $this->penulis_nama,
            'penulis_jabatan' => $this->penulis_jabatan,
            'waktu_baca_menit' => $this->waktu_baca_menit,
            'cover_image' => $this->cover_image,
            'cover_url' => $this->cover_url,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'published_at' => $this->published_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
