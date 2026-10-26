<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BroadcastAnnouncement;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $announcements = BroadcastAnnouncement::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('audience_role')->orWhere('audience_role', 'all')->orWhere('audience_role', $user->role->value))
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'important' THEN 2 ELSE 3 END")
            ->latest('published_at')
            ->get(['id', 'title', 'content', 'audience_role', 'priority', 'published_at', 'expires_at']);

        return response()->json(['success' => true, 'data' => $announcements]);
    }
}
