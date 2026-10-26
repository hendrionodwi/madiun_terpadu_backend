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
        Schema::create('tps3r_waste_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengelola_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tps3r_profile_id')->nullable()->constrained('pengelola_tps3r_profiles')->cascadeOnDelete();
            $table->foreignId('petugas_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('nomor_resi', 50)->unique();
            $table->string('sumber_sampah'); // armada_kelurahan, warga_mandiri, pemrakarsa_komersial
            $table->string('jenis_sampah')->index(); // organik, anorganik_daur_ulang, residu
            $table->decimal('berat_kg', 10, 2);
            $table->date('tanggal_setor')->index();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tps3r_waste_deposits');
    }
};
