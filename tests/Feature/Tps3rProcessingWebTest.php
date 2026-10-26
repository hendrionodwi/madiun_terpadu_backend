<?php

use App\Models\PengelolaTps3rProfile;
use App\Models\User;
use App\Models\WasteProcessingRecord;

test('pengelola tps3r can view waste processing dashboard page without blade errors', function () {
    $user = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->create([
        'user_id' => $user->id,
        'nama_tps3r' => 'TPS3R Manguharjo Berdikari',
    ]);

    // Create a sample record with output_weight_kg to test table rendering
    WasteProcessingRecord::create([
        'pengelola_id' => $user->id,
        'tps3r_profile_id' => $profile->id,
        'process_method' => 'composting',
        'waste_type' => 'organik',
        'input_weight_kg' => 50.0,
        'output_weight_kg' => 20.0,
        'started_on' => now(),
        'status' => 'completed',
        'notes' => 'Batch uji coba',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard.tps3r.processing.index'));

    $response->assertOk()
        ->assertSee('Proses Pengolahan Sampah')
        ->assertSee('Operasional TPS3R')
        ->assertSee('Buat batch pengolahan')
        ->assertSee('Masuk 50,0 kg')
        ->assertSee('Hasil 20,0 kg');
});
