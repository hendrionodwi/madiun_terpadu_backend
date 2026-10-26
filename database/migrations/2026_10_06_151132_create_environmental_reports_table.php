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
        Schema::create('environmental_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemrakarsa_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul_laporan');
            $table->string('periode_semester'); // Semester 1 (Jan - Jun), Semester 2 (Jul - Des)
            $table->unsignedSmallInteger('tahun');
            $table->string('jenis_dokumen'); // SPPL, UKL-UPL, AMDAL
            $table->decimal('timbulan_sampah_kg', 10, 2)->nullable();
            $table->text('metode_pengelolaan')->nullable();
            $table->string('file_path');
            $table->string('status')->default('menunggu_verifikasi')->index(); // menunggu_verifikasi, disetujui, perlu_revisi
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('environmental_reports');
    }
};
