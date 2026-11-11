<?php

namespace App\Http\Resources\Api;

use App\Models\BroadcastAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin BroadcastAnnouncement */
class BroadcastAnnouncementResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'summary' => $this->summary,
            'content' => $this->content,
            'audience_role' => $this->audience_role,
            'priority' => $this->priority,
            'status' => $this->status,
            'kategori' => $this->kategori,
            'is_active' => $this->is_active,
            'show_on_portal' => $this->show_on_portal,
            'published_at' => $this->published_at?->toISOString(),
            'expires_at' => $this->expires_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
