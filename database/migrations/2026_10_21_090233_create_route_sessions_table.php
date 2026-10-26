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
        Schema::create('route_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_assignment_id')->constrained('route_assignments')->cascadeOnDelete();
            $table->foreignId('petugas_id')->constrained('users')->cascadeOnDelete();
            $table->string('route_code', 50)->index();
            $table->date('tanggal')->index();
            $table->string('status', 30)->default('terjadwal')->index(); // terjadwal, berlangsung, selesai, dibatalkan
            $table->time('waktu_mulai')->nullable();
            $table->time('waktu_selesai')->nullable();
            $table->string('start_location', 150)->nullable();
            $table->string('end_facility', 150)->nullable();
            $table->decimal('start_latitude', 10, 7)->nullable();
            $table->decimal('start_longitude', 10, 7)->nullable();
            $table->decimal('end_latitude', 10, 7)->nullable();
            $table->decimal('end_longitude', 10, 7)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['route_assignment_id', 'tanggal']);
            $table->index(['petugas_id', 'tanggal', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_sessions');
    }
};
