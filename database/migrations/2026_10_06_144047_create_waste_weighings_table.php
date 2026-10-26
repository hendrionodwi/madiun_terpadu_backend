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
        Schema::create('waste_weighings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warga_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('petugas_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pickup_schedule_id')->nullable()->constrained('pickup_schedules')->cascadeOnDelete();
            $table->string('jenis_sampah')->index(); // organik, anorganik_plastik, anorganik_kertas, anorganik_logam, residu
            $table->decimal('berat_kg', 8, 2);
            $table->unsignedInteger('poin_per_kg')->default(100);
            $table->unsignedInteger('poin_didapat')->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_weighings');
    }
};
