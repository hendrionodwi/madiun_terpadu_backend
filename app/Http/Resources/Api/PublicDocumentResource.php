<?php

namespace App\Http\Resources\Api;

use App\Models\PublicDocument;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PublicDocument */
class PublicDocumentResource extends JsonResource
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
            'deskripsi' => $this->deskripsi,
            'download_url' => url("/api/v1/public/documents/{$this->slug}/download"),
            'file_format' => $this->file_format,
            'file_size_bytes' => $this->file_size_bytes,
            'formatted_size' => $this->formatted_size,
            'tanggal_rilis' => $this->tanggal_rilis?->toDateString(),
            'hak_akses_label' => $this->hak_akses_label,
            'status' => $this->status,
            'download_count' => $this->download_count,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
