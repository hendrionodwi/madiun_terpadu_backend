<?php

namespace Database\Seeders;

use App\Models\FundWithdrawalRequest;
use Illuminate\Database\Seeder;

class FundWithdrawalRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FundWithdrawalRequest::factory()->count(3)->create();
    }
}
