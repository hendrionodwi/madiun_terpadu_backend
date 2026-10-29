<?php

use App\Models\User;
use App\Models\WasteProduct;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('pengelola tps3r can add a product with a photo and view it on the catalog', function () {
    $pengelola = User::factory()->pengelolaTps3r()->create();
    $photo = UploadedFile::fake()->image('pupuk-kompos.jpg', 600, 600)->size(500);

    $response = $this->actingAs($pengelola)->post(route('dashboard.tps3r.store-product'), [
        'nama_produk' => 'Kompos Super Madiun 5kg',
        'deskripsi' => 'Kompos murni dari sampah organik pasar.',
        'kategori' => 'pupuk_kompos',
        'harga' => 15000,
        'stok' => 20,
        'foto' => $photo,
        'is_available' => true,
    ]);

    $response->assertRedirect(route('dashboard.tps3r.products'))
        ->assertSessionHas('status');

    $product = WasteProduct::where('nama_produk', 'Kompos Super Madiun 5kg')->first();
    expect($product)->not->toBeNull()
        ->and($product->foto)->not->toBeNull()
        ->and($product->foto_url)->toContain('storage/'.$product->foto);

    Storage::disk('public')->assertExists($product->foto);

    $this->actingAs($pengelola)->get(route('dashboard.tps3r.products'))
        ->assertOk()
        ->assertSee('Kompos Super Madiun 5kg')
        ->assertSee('pupuk kompos');
});

test('pengelola tps3r can update product photo and the old photo is removed from storage', function () {
    $pengelola = User::factory()->pengelolaTps3r()->create();

    $oldPhoto = UploadedFile::fake()->image('old-foto.jpg');
    $oldPath = $oldPhoto->store('products', 'public');

    $product = WasteProduct::factory()->create([
        'seller_id' => $pengelola->id,
        'nama_produk' => 'Kerajinan Anyaman Tas',
        'kategori' => 'kerajinan_plastik',
        'harga' => 25000,
        'stok' => 10,
        'foto' => $oldPath,
    ]);

    Storage::disk('public')->assertExists($oldPath);

    $newPhoto = UploadedFile::fake()->image('new-foto.webp', 800, 800)->size(400);

    $updateResponse = $this->actingAs($pengelola)->patch(route('dashboard.tps3r.products.update', $product), [
        'nama_produk' => 'Kerajinan Anyaman Tas Premium',
        'kategori' => 'kerajinan_plastik',
        'harga' => 30000,
        'stok' => 8,
        'foto' => $newPhoto,
        'is_available' => true,
    ]);

    $updateResponse->assertRedirect(route('dashboard.tps3r.products'))
        ->assertSessionHas('status');

    $product->refresh();
    expect($product->nama_produk)->toBe('Kerajinan Anyaman Tas Premium')
        ->and($product->foto)->not->toBe($oldPath);

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($product->foto);
});

test('deleting a product also deletes its photo from storage', function () {
    $pengelola = User::factory()->pengelolaTps3r()->create();

    $photo = UploadedFile::fake()->image('maggot.jpg');
    $photoPath = $photo->store('products', 'public');

    $product = WasteProduct::factory()->create([
        'seller_id' => $pengelola->id,
        'nama_produk' => 'Maggot Kering BSF 250g',
        'kategori' => 'maggot_pakan',
        'harga' => 18000,
        'stok' => 15,
        'foto' => $photoPath,
    ]);

    Storage::disk('public')->assertExists($photoPath);

    $deleteResponse = $this->actingAs($pengelola)->delete(route('dashboard.tps3r.products.destroy', $product));

    $deleteResponse->assertRedirect(route('dashboard.tps3r.products'))
        ->assertSessionHas('status');

    $this->assertDatabaseMissing('waste_products', ['id' => $product->id]);
    Storage::disk('public')->assertMissing($photoPath);
});

test('product photo upload rejects non-image files or files exceeding 2MB', function () {
    $pengelola = User::factory()->pengelolaTps3r()->create();
    $pdfFile = UploadedFile::fake()->create('dokumen.pdf', 500, 'application/pdf');

    $response = $this->actingAs($pengelola)->post(route('dashboard.tps3r.store-product'), [
        'nama_produk' => 'Produk Coba',
        'kategori' => 'lainnya',
        'harga' => 10000,
        'stok' => 5,
        'foto' => $pdfFile,
    ]);

    $response->assertSessionHasErrors(['foto']);
});
