<?php

namespace Database\Seeders;

use App\Models\EntityVerification;
use Illuminate\Database\Seeder;

class EntityVerificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EntityVerification::factory()->count(2)->create();
    }
}
