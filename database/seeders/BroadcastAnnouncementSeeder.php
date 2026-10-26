<?php

namespace Database\Seeders;

use App\Models\BroadcastAnnouncement;
use App\Models\User;
use Illuminate\Database\Seeder;

class BroadcastAnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::query()->where('role', 'admin_dlh')->first();

        if ($admin === null) {
            return;
        }

        BroadcastAnnouncement::firstOrCreate(
            ['title' => 'Selamat datang di Madiun Terpadu'],
            [
                'created_by' => $admin->id,
                'content' => 'Gunakan layanan sesuai peran Anda untuk mendukung pengelolaan lingkungan Kota Madiun.',
                'audience_role' => null,
                'priority' => 'normal',
                'is_active' => true,
                'published_at' => now(),
                'expires_at' => now()->addYear(),
            ],
        );
    }
}
