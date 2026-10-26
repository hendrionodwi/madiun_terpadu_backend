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
        Schema::create('point_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warga_id')->constrained('users')->cascadeOnDelete();
            $table->enum('tipe', ['kredit', 'debit']);
            $table->string('kategori', 50); // setor_sampah, belanja_marketplace, penukaran_kas, bayar_retribusi, pengembalian_batal, penyesuaian_admin
            $table->integer('jumlah_poin');
            $table->integer('saldo_sebelumnya');
            $table->integer('saldo_setelahnya');
            $table->string('referensi_tipe', 100)->nullable();
            $table->unsignedBigInteger('referensi_id')->nullable();
            $table->string('deskripsi', 255);
            $table->timestamps();

            $table->index(['warga_id', 'created_at']);
            $table->index(['referensi_tipe', 'referensi_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_ledgers');
    }
};
