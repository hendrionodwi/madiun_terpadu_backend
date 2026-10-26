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
        Schema::create('pickup_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('petugas_id')->constrained('users')->cascadeOnDelete();
            $table->string('id_wilayah_rute')->index();
            $table->string('nama_wilayah_rute')->nullable();
            $table->date('tanggal_penjemputan')->index();
            $table->string('waktu_mulai', 10);
            $table->string('waktu_selesai', 10);
            $table->string('status')->default('terjadwal')->index(); // terjadwal, berlangsung, selesai, dibatalkan
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pickup_schedules');
    }
};
