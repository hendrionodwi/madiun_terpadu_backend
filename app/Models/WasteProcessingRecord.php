<?php

namespace App\Models;

use Database\Factories\WasteProcessingRecordFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'pengelola_id',
    'tps3r_profile_id',
    'tps3r_waste_deposit_id',
    'process_method',
    'waste_type',
    'input_weight_kg',
    'output_weight_kg',
    'status',
    'started_on',
    'completed_on',
    'notes',
])]
class WasteProcessingRecord extends Model
{
    /** @use HasFactory<WasteProcessingRecordFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'input_weight_kg' => 'float',
            'output_weight_kg' => 'float',
            'started_on' => 'date',
            'completed_on' => 'date',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function pengelola(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengelola_id');
    }

    /** @return BelongsTo<PengelolaTps3rProfile, $this> */
    public function tps3rProfile(): BelongsTo
    {
        return $this->belongsTo(PengelolaTps3rProfile::class, 'tps3r_profile_id');
    }

    /** @return BelongsTo<Tps3rWasteDeposit, $this> */
    public function deposit(): BelongsTo
    {
        return $this->belongsTo(Tps3rWasteDeposit::class, 'tps3r_waste_deposit_id');
    }
}
