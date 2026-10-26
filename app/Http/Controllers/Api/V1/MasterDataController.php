<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MasterData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MasterDataController extends Controller
{
    /**
     * Dapatkan daftar data master sistem terpusat (kategori sampah, tarif poin, dll).
     */
    public function index(Request $request): JsonResponse
    {
        $group = $request->query('group');
        $query = MasterData::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name');

        if ($group) {
            $normalizedGroups = match ($group) {
                'complaint_categories', 'complaint_type', 'complaint_types' => ['complaint_categories', 'complaint_type', 'complaint_types'],
                'waste_category', 'waste_categories' => ['waste_category', 'waste_categories'],
                'point_conversion', 'point_conversions' => ['point_conversion', 'point_conversions'],
                'district', 'districts' => ['districts', 'district'],
                default => [$group],
            };
            $query->whereIn('group', $normalizedGroups);
        }

        $items = $query->get()->map(function (MasterData $item) {
            return [
                'id' => $item->id,
                'group' => $item->group,
                'code' => $item->code,
                'pillar' => $item->pillar ?? MasterData::resolvePillar($item->code),
                'name' => $item->name,
                'numeric_value' => $item->numeric_value,
                'unit' => $item->unit,
                'description' => $item->description,
                'sort_order' => $item->sort_order,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Data master berhasil dimuat.',
            'data' => $items,
        ], Response::HTTP_OK);
    }
}
