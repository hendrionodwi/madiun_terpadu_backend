<?php

use App\Models\MasterData;
use App\Models\PengelolaTps3rProfile;
use App\Models\User;
use App\Models\WasteProcessingRecord;

test('pengelola tps3r can view waste processing dashboard page without blade errors', function () {
    $user = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->create([
        'user_id' => $user->id,
        'nama_tps3r' => 'TPS3R Manguharjo Berdikari',
    ]);

    MasterData::create([
        'group' => 'waste_category',
        'code' => 'KARDUS_BEKAS',
        'pillar' => 'residu',
        'name' => 'Kardus Bekas Box',
        'is_active' => true,
        'sort_order' => 1,
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
        ->assertSee('Kardus Bekas Box')
        ->assertSee('3 Pilar Utama Sampah Kota Madiun')
        ->assertSee('Masuk: 50,0 kg')
        ->assertSee('Hasil: 20,0 kg');
});

test('pengelola tps3r can create batch using master data waste category and transition status cleanly', function () {
    $user = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->create([
        'user_id' => $user->id,
        'nama_tps3r' => 'TPS3R Manguharjo Berdikari',
    ]);

    MasterData::create([
        'group' => 'waste_category',
        'code' => 'BOTOL_PLASTIK_PET',
        'pillar' => 'anorganik_daur_ulang',
        'name' => 'Botol Plastik PET Bening',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $response = $this->actingAs($user)->post(route('dashboard.tps3r.processing.store'), [
        'process_method' => 'shredding',
        'waste_type' => 'BOTOL_PLASTIK_PET',
        'input_weight_kg' => 25.5,
        'started_on' => now()->toDateString(),
        'notes' => 'Pencacahan botol plastik master data',
    ]);

    $response->assertRedirect()
        ->assertSessionHas('status');

    $record = WasteProcessingRecord::where('waste_type', 'BOTOL_PLASTIK_PET')->first();
    expect($record)->not->toBeNull();
    expect($record->status)->toBe('planned');
    expect($record->waste_type_label)->toBe('Botol Plastik PET Bening');

    // Test transition from planned to processing
    $updateResponse = $this->actingAs($user)->patch(route('dashboard.tps3r.processing.update', $record), [
        'status' => 'processing',
    ]);

    $updateResponse->assertRedirect()
        ->assertSessionHas('status');
    expect($record->fresh()->status)->toBe('processing');

    // Test transition from processing to completed with output_weight_kg
    $completeResponse = $this->actingAs($user)->patch(route('dashboard.tps3r.processing.update', $record), [
        'status' => 'completed',
        'output_weight_kg' => 24.0,
    ]);

    $completeResponse->assertRedirect()
        ->assertSessionHas('status');
    expect($record->fresh()->status)->toBe('completed');
    expect($record->fresh()->output_weight_kg)->toBe(24.0);
});
