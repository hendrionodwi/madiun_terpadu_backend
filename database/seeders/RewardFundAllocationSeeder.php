<?php

namespace Database\Seeders;

use App\Models\RewardFundAllocation;
use App\Models\User;
use Illuminate\Database\Seeder;

class RewardFundAllocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin_dlh')->first() ?? User::factory()->create(['role' => 'admin_dlh']);

        RewardFundAllocation::firstOrCreate(
            ['reference_number' => 'DPA-DLH/2026/01/PILAH'],
            [
                'created_by' => $admin->id,
                'source_type' => 'apbd',
                'amount' => 50000000,
                'fiscal_year' => 2026,
                'allocated_at' => now()->startOfYear(),
                'notes' => 'Alokasi APBD Induk TA 2026 Pos Program Insentif Pilah Sampah Mandiri Kota Madiun',
            ]
        );

        RewardFundAllocation::firstOrCreate(
            ['reference_number' => 'BKM-TPS3R/2026/03/KOMPOS'],
            [
                'created_by' => $admin->id,
                'source_type' => 'penjualan_daur_ulang',
                'amount' => 15000000,
                'fiscal_year' => 2026,
                'allocated_at' => now()->subMonth(),
                'notes' => 'Penyaluran kas hasil penjualan pupuk kompos dan bahan daur ulang TPS3R Winongo ke kas reward',
            ]
        );
    }
}
