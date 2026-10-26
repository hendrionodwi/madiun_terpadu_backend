<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RewardFundAllocation extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'created_by',
        'source_type',
        'amount',
        'fiscal_year',
        'allocated_at',
        'reference_number',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'fiscal_year' => 'integer',
            'allocated_at' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source_type) {
            'apbd' => 'Alokasi APBD Kota Madiun',
            'penjualan_daur_ulang' => 'Hasil Penjualan Kompos & Daur Ulang',
            'csr_kemitraan' => 'Kemitraan Swasta / CSR / EPR',
            'retribusi' => 'Penerimaan Retribusi Persampahan',
            default => 'Lain-lain',
        };
    }
}
