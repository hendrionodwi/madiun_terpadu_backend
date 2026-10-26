<?php

namespace Database\Seeders;

use App\Models\WasteProcessingRecord;
use Illuminate\Database\Seeder;

class WasteProcessingRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WasteProcessingRecord::factory()->count(3)->create();
    }
}
