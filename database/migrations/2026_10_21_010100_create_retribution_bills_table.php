<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('retribution_bills', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_tagihan', 50)->unique();
            $table->foreignId('warga_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('periode_bulan');
            $table->unsignedSmallInteger('periode_tahun');
            $table->decimal('nominal_tagihan', 12, 2);
            $table->unsignedInteger('poin_digunakan')->default(0);
            $table->decimal('nominal_potongan_poin', 12, 2)->default(0);
            $table->decimal('nominal_dibayar', 12, 2)->default(0);
            $table->string('metode_pembayaran', 50)->default('belum_dipilih'); // belum_dipilih, poin_sampah, midtrans, hibrida, manual_dlh
            $table->enum('status', ['belum_dibayar', 'menunggu_pembayaran', 'lunas', 'dibatalkan'])->default('belum_dibayar');
            $table->date('jatuh_tempo');
            $table->timestamp('paid_at')->nullable();
            $table->string('snap_token', 100)->nullable();
            $table->string('snap_redirect_url', 500)->nullable();
            $table->json('payment_gateway_response')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['warga_id', 'status']);
            $table->index(['periode_tahun', 'periode_bulan']);
            $table->index('jatuh_tempo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retribution_bills');
    }
};
