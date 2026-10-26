<?php

namespace App\Models;

use Database\Factories\MasterDataFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'group',
    'code',
    'pillar',
    'name',
    'numeric_value',
    'unit',
    'description',
    'is_active',
    'sort_order',
])]
class MasterData extends Model
{
    /** @use HasFactory<MasterDataFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'numeric_value' => 'float',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Resolusi pilar induk neraca sampah (organik, anorganik_daur_ulang, residu).
     */
    public static function resolvePillar(?string $categoryCode): string
    {
        if (empty($categoryCode)) {
            return 'organik';
        }

        $code = strtolower(trim($categoryCode));

        // Cek data master jika ada record dengan pillar eksplisit
        $record = static::query()
            ->where('group', 'waste_category')
            ->where(function ($q) use ($code) {
                $q->where('code', $code)
                    ->orWhere('name', $code);
            })
            ->first();

        if ($record && ! empty($record->pillar)) {
            return $record->pillar;
        }

        if ($code === 'organik' || (str_contains($code, 'organik') && ! str_contains($code, 'anorganik')) || str_contains($code, 'kompos') || str_contains($code, 'daun')) {
            return 'organik';
        }

        if (str_contains($code, 'residu') || str_contains($code, 'tpa') || str_contains($code, 'b3') || str_contains($code, 'popok')) {
            return 'residu';
        }

        return 'anorganik_daur_ulang';
    }
}
