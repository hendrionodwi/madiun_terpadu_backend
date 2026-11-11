<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BroadcastAnnouncementResource;
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
        $announcements = BroadcastAnnouncement::published()
            ->where(fn ($query) => $query
                ->whereNull('audience_role')
                ->orWhere('audience_role', 'all')
                ->orWhere('audience_role', $user->role->value))
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'important' THEN 2 ELSE 3 END")
            ->latest('published_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => BroadcastAnnouncementResource::collection($announcements),
        ]);
    }
}
