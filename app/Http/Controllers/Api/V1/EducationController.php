<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MasterData;
use Illuminate\Http\JsonResponse;

class EducationController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = MasterData::query()
            ->where('group', 'waste_category')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['code', 'name', 'numeric_value', 'unit', 'description']);

        return response()->json([
            'success' => true,
            'message' => 'Materi edukasi pemilahan sampah berhasil diambil.',
            'data' => $categories,
        ]);
    }
}
