<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleProfileSeeder::class);
        $this->call(OrderFulfillmentSeeder::class);
        $this->call(MasterDataSeeder::class);
        $this->call(BroadcastAnnouncementSeeder::class);
        $this->call(RouteAssignmentSeeder::class);
        $this->call(PickupScheduleSeeder::class);
        $this->call(EntityVerificationSeeder::class);
        $this->call(RewardFundAllocationSeeder::class);
        $this->call(FundWithdrawalRequestSeeder::class);
        $this->call(WasteProcessingRecordSeeder::class);
        $this->call(ActivityLogSeeder::class);

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
            ]
        );
    }
}
