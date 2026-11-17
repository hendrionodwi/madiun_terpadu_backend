<?php

namespace App\Services;

use App\Models\MasterData;

class PointConversionService
{
    public function currentRate(): float
    {
        $configuredRate = (float) (MasterData::query()
            ->whereIn('group', ['point_conversion', 'point_conversions'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->value('numeric_value') ?? config('finance.points.default_conversion_rate'));

        $minimumRate = (float) config('finance.points.minimum_conversion_rate');
        $maximumRate = (float) config('finance.points.maximum_conversion_rate');

        if ($configuredRate < $minimumRate || $configuredRate > $maximumRate) {
            return (float) config('finance.points.default_conversion_rate');
        }

        return $configuredRate;
    }

    public function convert(int $points): float
    {
        return round($points * $this->currentRate(), 2);
    }
}
