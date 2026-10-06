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
        Schema::create('petugas_lapangan_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('nip')->nullable();
            $table->string('id_wilayah_rute')->nullable()->index();
            $table->string('nama_wilayah_tugas')->nullable();
            $table->string('jenis_kendaraan')->nullable();
            $table->string('plat_nomor')->nullable();
            $table->string('status_tugas')->default('standby');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petugas_lapangan_profiles');
    }
};
