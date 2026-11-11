<?php

use App\Enums\OrderFulfillmentStatus;
use App\Models\EducationArticle;
use App\Models\EntityVerification;
use App\Models\EnvironmentalReport;
use App\Models\FundWithdrawalRequest;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\OrderItem;
use App\Models\PengelolaTps3rProfile;
use App\Models\PointLedger;
use App\Models\PublicDocument;
use App\Models\User;
use App\Models\WargaProfile;
use App\Models\WasteProduct;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

test('warga checkout enforces server-side pricing, active seller, safe order identifiers, and single point refund', function () {
    $buyer = User::factory()->warga()->create();
    $otherWarga = User::factory()->warga()->create();
    $buyerProfile = WargaProfile::factory()->create(['user_id' => $buyer->id, 'saldo_poin' => 50000]);

    $activeSeller = User::factory()->pengelolaTps3r()->create(['is_active' => true]);
    $inactiveSeller = User::factory()->pengelolaTps3r()->create(['is_active' => false]);

    $activeProduct = WasteProduct::factory()->create(['seller_id' => $activeSeller->id, 'harga' => 15000, 'stok' => 5]);
    $inactiveProduct = WasteProduct::factory()->create(['seller_id' => $inactiveSeller->id, 'harga' => 10000, 'stok' => 5]);

    Sanctum::actingAs($buyer, ['role:warga']);

    // Produk dari toko non-aktif ditolak 422
    $this->postJson('/api/v1/marketplace/checkout', [
        'items' => [['product_id' => $inactiveProduct->id, 'jumlah' => 1]],
        'metode_pembayaran' => 'cod',
        'alamat_pengiriman' => 'Jl. Pahlawan No. 1 Madiun',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['items']);
    expect($inactiveProduct->fresh()->stok)->toBe(5);

    // Payload harga dimanipulasi di client harus diabaikan; server hitung 2 * 15000 = 30000
    $checkout = $this->postJson('/api/v1/marketplace/checkout', [
        'items' => [['product_id' => $activeProduct->id, 'jumlah' => 2, 'harga_satuan' => 1]],
        'total_harga' => 1,
        'status' => 'selesai',
        'metode_pembayaran' => 'poin_sampah',
        'alamat_pengiriman' => 'Jl. Pahlawan No. 1 Madiun',
    ]);

    $checkout->assertCreated()
        ->assertJsonPath('data.total_harga', 30000)
        ->assertJsonPath('data.status', 'dibayar');

    $orderId = $checkout->json('data.id');
    $orderNumber = $checkout->json('data.nomor_pesanan');

    expect($activeProduct->fresh()->stok)->toBe(3);
    expect($buyerProfile->fresh()->saldo_poin)->toBe(20000);

    // Warga pemilik mengakses pakai nomor_pesanan; numeric id harus 404
    $this->getJson("/api/v1/marketplace/orders/{$orderNumber}")->assertOk();
    $this->getJson("/api/v1/marketplace/orders/{$orderId}")->assertNotFound();

    // Warga lain ditolak 403
    Sanctum::actingAs($otherWarga, ['role:warga']);
    $this->getJson("/api/v1/marketplace/orders/{$orderNumber}")->assertForbidden();
    $this->patchJson("/api/v1/marketplace/orders/{$orderNumber}/cancel")->assertForbidden();

    // Pembatalan pertama mengembalikan stok dan poin 1x; pembatalan ulang ditolak 422
    Sanctum::actingAs($buyer, ['role:warga']);
    $this->patchJson("/api/v1/marketplace/orders/{$orderNumber}/cancel")->assertOk();
    $this->patchJson("/api/v1/marketplace/orders/{$orderNumber}/cancel")->assertUnprocessable();

    expect($activeProduct->fresh()->stok)->toBe(5);
    expect($buyerProfile->fresh()->saldo_poin)->toBe(50000);
    expect(PointLedger::where('warga_id', $buyer->id)->where('kategori', 'refund_marketplace')->count())->toBe(1);
});

test('tps3r fulfillment prevents unpaid midtrans advance, duplicate cancel point inflation, and invalid store withdrawal', function () {
    $buyer = User::factory()->warga()->create();
    $buyerProfile = WargaProfile::factory()->create(['user_id' => $buyer->id, 'saldo_poin' => 0]);

    $sellerA = User::factory()->pengelolaTps3r()->create();
    PengelolaTps3rProfile::factory()->withMidtrans()->create(['user_id' => $sellerA->id]);
    $sellerB = User::factory()->pengelolaTps3r()->create();

    $productPoint = WasteProduct::factory()->create(['seller_id' => $sellerA->id, 'harga' => 12000, 'stok' => 8]);
    $productMidtrans = WasteProduct::factory()->create(['seller_id' => $sellerA->id, 'harga' => 40000, 'stok' => 5]);

    // 1. Pesanan dibayar poin_sampah lalu dibatalkan berulang via seller fulfillment
    $pointOrder = Order::factory()->create([
        'buyer_id' => $buyer->id,
        'total_harga' => 24000,
        'metode_pembayaran' => 'poin_sampah',
        'status' => 'dibayar',
    ]);
    OrderItem::factory()->create([
        'order_id' => $pointOrder->id,
        'product_id' => $productPoint->id,
        'jumlah' => 2,
        'harga_satuan' => 12000,
        'subtotal' => 24000,
    ]);
    $pointFulfillment = OrderFulfillment::factory()->create([
        'order_id' => $pointOrder->id,
        'seller_id' => $sellerA->id,
        'status' => OrderFulfillmentStatus::Menunggu,
    ]);

    // Seller B tidak dapat melihat/mengubah fulfillment Seller A
    Sanctum::actingAs($sellerB, ['role:pengelola_tps3r']);
    $this->getJson("/api/v1/marketplace/fulfillments/{$pointFulfillment->public_id}")->assertNotFound();
    $this->patchJson("/api/v1/marketplace/fulfillments/{$pointFulfillment->public_id}", [
        'status' => OrderFulfillmentStatus::Dibatalkan->value,
    ])->assertNotFound();

    // Seller A membatalkan dua kali berturut-turut -> stok & refund poin wajib terjadi tepat 1x melalui PointLedger
    Sanctum::actingAs($sellerA, ['role:pengelola_tps3r']);
    $this->patchJson("/api/v1/marketplace/fulfillments/{$pointFulfillment->public_id}", [
        'status' => OrderFulfillmentStatus::Dibatalkan->value,
        'catatan_status' => 'Stok rusak saat sortir',
    ])->assertOk()->assertJsonPath('data.status', 'dibatalkan');

    $this->patchJson("/api/v1/marketplace/fulfillments/{$pointFulfillment->public_id}", [
        'status' => OrderFulfillmentStatus::Dibatalkan->value,
    ])->assertOk()->assertJsonPath('data.status', 'dibatalkan');

    expect($productPoint->fresh()->stok)->toBe(10); // 8 + 2 tepat 1x
    expect($buyerProfile->fresh()->saldo_poin)->toBe(24000);
    expect(PointLedger::where('warga_id', $buyer->id)->where('kategori', 'refund_marketplace')->count())->toBe(1);

    // 2. Pesanan Midtrans yang masih pending (belum dibayar) dilarang diproses/diselesaikan atau dicairkan saldonya
    $unpaidOrder = Order::factory()->create([
        'buyer_id' => $buyer->id,
        'total_harga' => 40000,
        'metode_pembayaran' => 'midtrans',
        'status' => 'pending',
    ]);
    OrderItem::factory()->create([
        'order_id' => $unpaidOrder->id,
        'product_id' => $productMidtrans->id,
        'jumlah' => 1,
        'harga_satuan' => 40000,
        'subtotal' => 40000,
    ]);
    $unpaidFulfillment = OrderFulfillment::factory()->create([
        'order_id' => $unpaidOrder->id,
        'seller_id' => $sellerA->id,
        'status' => OrderFulfillmentStatus::Menunggu,
    ]);

    $this->patchJson("/api/v1/marketplace/fulfillments/{$unpaidFulfillment->public_id}", [
        'status' => OrderFulfillmentStatus::Diproses->value,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);

    // Saldo toko tetap 0 -> penarikan store ditolak 422
    $this->postJson('/api/v1/fund-withdrawals', [
        'source' => 'store',
        'amount' => 15000,
        'destination_type' => 'bank_transfer',
        'destination_account' => '987654321',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['amount']);

    // Pembeli juga dilarang confirm-received pada order Midtrans yang masih pending
    Sanctum::actingAs($buyer, ['role:warga']);
    $this->patchJson("/api/v1/marketplace/orders/{$unpaidOrder->nomor_pesanan}/confirm-received")
        ->assertUnprocessable();

    // Warga dilarang mengajukan pencairan sumber 'store'
    $this->postJson('/api/v1/fund-withdrawals', [
        'source' => 'store',
        'amount' => 15000,
        'destination_type' => 'bank_transfer',
        'destination_account' => '987654321',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['source']);
});

test('midtrans marketplace webhook enforces gross amount check, lock idempotency, and terminal conflict protection', function () {
    $seller = User::factory()->pengelolaTps3r()->create();
    $secret = 'secret-midtrans-crossrole';
    PengelolaTps3rProfile::factory()->withMidtrans()->create([
        'user_id' => $seller->id,
        'midtrans_signature_secret' => $secret,
    ]);

    $product = WasteProduct::factory()->create(['seller_id' => $seller->id, 'harga' => 50000, 'stok' => 4]);

    // Case A: Tampered gross_amount ditolak 422; settlement idempoten tidak bisa ditimpa expire/pending
    $paidOrder = Order::factory()->create([
        'nomor_pesanan' => 'ORD-CROSS-PAID-01',
        'total_harga' => 50000,
        'metode_pembayaran' => 'midtrans',
        'status' => 'pending',
    ]);
    $paidOrder->items()->create([
        'product_id' => $product->id,
        'jumlah' => 1,
        'harga_satuan' => 50000,
        'subtotal' => 50000,
    ]);

    $tamperedGross = '100.00';
    $tamperedSig = hash('sha512', $paidOrder->nomor_pesanan.'200'.$tamperedGross.$secret);
    $this->postJson('/api/v1/marketplace/midtrans/notification', [
        'order_id' => $paidOrder->nomor_pesanan,
        'status_code' => '200',
        'gross_amount' => $tamperedGross,
        'transaction_status' => 'settlement',
        'signature_key' => $tamperedSig,
    ])->assertUnprocessable();
    expect($paidOrder->fresh()->status)->toBe('pending');

    $validGross = '50000.00';
    $validSig = hash('sha512', $paidOrder->nomor_pesanan.'200'.$validGross.$secret);
    $this->postJson('/api/v1/marketplace/midtrans/notification', [
        'order_id' => $paidOrder->nomor_pesanan,
        'status_code' => '200',
        'gross_amount' => $validGross,
        'transaction_status' => 'settlement',
        'signature_key' => $validSig,
    ])->assertOk();

    $firstPaidAt = $paidOrder->fresh()->paid_at;
    expect($paidOrder->fresh()->status)->toBe('dibayar')
        ->and($firstPaidAt)->not->toBeNull();

    // Replay settlement tetap 200 tanpa mengubah paid_at; expire setelah bayar ditolak 409 tanpa restock
    $this->postJson('/api/v1/marketplace/midtrans/notification', [
        'order_id' => $paidOrder->nomor_pesanan,
        'status_code' => '200',
        'gross_amount' => $validGross,
        'transaction_status' => 'settlement',
        'signature_key' => $validSig,
    ])->assertOk();
    expect($paidOrder->fresh()->paid_at?->toISOString())->toBe($firstPaidAt?->toISOString());

    $this->postJson('/api/v1/marketplace/midtrans/notification', [
        'order_id' => $paidOrder->nomor_pesanan,
        'status_code' => '200',
        'gross_amount' => $validGross,
        'transaction_status' => 'expire',
        'signature_key' => $validSig,
    ])->assertConflict();
    expect($product->fresh()->stok)->toBe(4);

    // Case B: Duplicate expire hanya restore stok 1x; settlement setelah dibatalkan ditolak 409
    $expiredOrder = Order::factory()->create([
        'nomor_pesanan' => 'ORD-CROSS-EXP-02',
        'total_harga' => 50000,
        'metode_pembayaran' => 'midtrans',
        'status' => 'pending',
    ]);
    $expiredOrder->items()->create([
        'product_id' => $product->id,
        'jumlah' => 2,
        'harga_satuan' => 25000,
        'subtotal' => 50000,
    ]);
    $expSig = hash('sha512', $expiredOrder->nomor_pesanan.'200'.$validGross.$secret);

    $this->postJson('/api/v1/marketplace/midtrans/notification', [
        'order_id' => $expiredOrder->nomor_pesanan,
        'status_code' => '200',
        'gross_amount' => $validGross,
        'transaction_status' => 'expire',
        'signature_key' => $expSig,
    ])->assertOk();

    $this->postJson('/api/v1/marketplace/midtrans/notification', [
        'order_id' => $expiredOrder->nomor_pesanan,
        'status_code' => '200',
        'gross_amount' => $validGross,
        'transaction_status' => 'expire',
        'signature_key' => $expSig,
    ])->assertOk();

    expect($expiredOrder->fresh()->status)->toBe('dibatalkan');
    expect($product->fresh()->stok)->toBe(6); // 4 + 2 tepat 1x

    $this->postJson('/api/v1/marketplace/midtrans/notification', [
        'order_id' => $expiredOrder->nomor_pesanan,
        'status_code' => '200',
        'gross_amount' => $validGross,
        'transaction_status' => 'settlement',
        'signature_key' => $expSig,
    ])->assertConflict();
    expect($expiredOrder->fresh()->status)->toBe('dibatalkan');
});

test('pemrakarsa drafts and reports are isolated from other pemrakarsa and cannot be verified before submission', function () {
    $pemrakarsaA = User::factory()->pemrakarsa()->create();
    $pemrakarsaB = User::factory()->pemrakarsa()->create();
    $admin = User::factory()->adminDlh()->create();

    $draftReport = EnvironmentalReport::factory()->create([
        'pemrakarsa_id' => $pemrakarsaA->id,
        'judul_laporan' => 'Draft Rahasia Pemrakarsa A',
        'status' => 'draft',
    ]);

    // Pemrakarsa B tidak boleh melihat/mengubah/menghapus laporan Pemrakarsa A via API maupun Web
    Sanctum::actingAs($pemrakarsaB, ['role:pemrakarsa']);
    $this->getJson("/api/v1/environmental-reports/{$draftReport->public_id}")->assertForbidden();
    $this->patchJson("/api/v1/environmental-reports/{$draftReport->public_id}", ['judul_laporan' => 'Hack'])->assertForbidden();
    $this->deleteJson("/api/v1/environmental-reports/{$draftReport->public_id}")->assertForbidden();
    $this->actingAs($pemrakarsaB)->patch(route('dashboard.pemrakarsa.reports.update', $draftReport), ['judul_laporan' => 'Hack'])->assertForbidden();
    $this->actingAs($pemrakarsaB)->delete(route('dashboard.pemrakarsa.reports.destroy', $draftReport))->assertForbidden();

    // Admin tidak boleh memverifikasi draft yang belum diajukan
    Sanctum::actingAs($admin, ['role:admin_dlh']);
    $this->patchJson("/api/v1/environmental-reports/{$draftReport->public_id}/verification", [
        'status' => 'disetujui',
    ])->assertUnprocessable();
    $this->actingAs($admin)->patch(route('dashboard.admin.reports.verify', $draftReport->public_id), [
        'status' => 'disetujui',
    ])->assertNotFound();

    // Pemrakarsa A mengajukan laporan lalu Admin menyetujui dengan nomor BAP otomatis
    Sanctum::actingAs($pemrakarsaA, ['role:pemrakarsa']);
    $this->patchJson("/api/v1/environmental-reports/{$draftReport->public_id}", [
        'status' => 'menunggu_verifikasi',
    ])->assertOk()->assertJsonPath('data.status', 'menunggu_verifikasi');

    Sanctum::actingAs($admin, ['role:admin_dlh']);
    $this->patchJson("/api/v1/environmental-reports/{$draftReport->public_id}/verification", [
        'status' => 'disetujui',
        'catatan_verifikasi' => 'Dokumen UKL-UPL lengkap.',
    ])->assertOk()->assertJsonPath('data.status', 'disetujui');

    expect($draftReport->fresh()->nomor_bap)->not->toBeNull();
});

test('admin rbac isolates private entity documents, prevents double withdrawal review, and portal hides non-published content', function () {
    Storage::fake('local');
    Storage::put('entity-documents/legal-pemrakarsa.pdf', '%PDF-1.4 confidential');
    Storage::disk('local')->put('documents/private-draft.pdf', '%PDF-1.4 draft');
    Storage::disk('local')->put('documents/public-valid.pdf', '%PDF-1.4 public');

    $admin = User::factory()->adminDlh()->create();
    $warga = User::factory()->warga()->create();
    WargaProfile::factory()->create(['user_id' => $warga->id, 'saldo_poin' => 1000]);
    $pemrakarsa = User::factory()->pemrakarsa()->create();

    $verification = EntityVerification::create([
        'user_id' => $pemrakarsa->id,
        'entity_type' => 'pemrakarsa',
        'document_number' => 'NIB-00112233',
        'document_path' => 'entity-documents/legal-pemrakarsa.pdf',
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);

    $withdrawal = FundWithdrawalRequest::create([
        'user_id' => $warga->id,
        'source' => 'points',
        'points_used' => 500,
        'amount' => 5000,
        'destination_type' => 'cash',
        'status' => 'pending',
    ]);

    // Warga / non-admin tidak boleh mengunduh dokumen legal entitas atau mengakses route admin
    $this->actingAs($warga)->get(route('dashboard.admin.entities.download', $verification))->assertForbidden();
    $this->actingAs($pemrakarsa)->get(route('dashboard.admin.entities.download', $verification))->assertForbidden();
    $this->actingAs($warga)->get(route('dashboard.admin.activity-logs'))->assertForbidden();

    // Admin DLH dapat mengunduh dokumen legal privat
    $this->actingAs($admin)->get(route('dashboard.admin.entities.download', $verification))->assertOk();

    // Penolakan pencairan mengembalikan poin tepat 1x; peninjauan ulang ditolak validation
    $this->actingAs($admin)->patch(route('dashboard.admin.points.update', $withdrawal), [
        'status' => 'rejected',
        'admin_note' => 'Verifikasi awal ditolak.',
    ])->assertRedirect();

    expect($warga->wargaProfile->fresh()->saldo_poin)->toBe(1500);
    expect(PointLedger::where('warga_id', $warga->id)->where('kategori', 'refund_penukaran_poin')->count())->toBe(1);

    $this->actingAs($admin)->patch(route('dashboard.admin.points.update', $withdrawal), [
        'status' => 'approved',
    ])->assertSessionHasErrors('status');
    expect($withdrawal->fresh()->status)->toBe('rejected');

    // Portal Publik menyembunyikan artikel draft/future & dokumen privat, hanya melayani yang tayang aktif
    EducationArticle::factory()->create([
        'created_by' => $admin->id,
        'judul' => 'Artikel Masa Depan Terjadwal',
        'status' => 'published',
        'published_at' => now()->addDays(5),
    ]);
    EducationArticle::factory()->create([
        'created_by' => $admin->id,
        'judul' => 'Artikel Edukasi Rilis Sah',
        'status' => 'published',
        'published_at' => now()->subMinute(),
    ]);
    $privateDoc = PublicDocument::factory()->create([
        'created_by' => $admin->id,
        'judul' => 'Dokumen Belum Terbit',
        'file_path' => 'documents/private-draft.pdf',
        'status' => 'draft',
    ]);
    $publicDoc = PublicDocument::factory()->create([
        'created_by' => $admin->id,
        'judul' => 'Dokumen Publik Aktif',
        'file_path' => 'documents/public-valid.pdf',
        'status' => 'published',
        'tanggal_rilis' => now()->subDay()->toDateString(),
    ]);

    $this->getJson('/api/v1/public/overview')
        ->assertOk()
        ->assertJsonMissing(['judul' => 'Artikel Masa Depan Terjadwal'])
        ->assertJsonFragment(['judul' => 'Artikel Edukasi Rilis Sah']);

    $this->getJson("/api/v1/public/documents/{$privateDoc->slug}/download")->assertNotFound();
    $this->getJson("/api/v1/public/documents/{$publicDoc->slug}/download")->assertOk();
});
