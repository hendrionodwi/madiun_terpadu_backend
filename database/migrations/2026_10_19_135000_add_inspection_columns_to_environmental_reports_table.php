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
        Schema::table('environmental_reports', function (Blueprint $table) {
            $table->text('catatan_inspeksi_petugas')->nullable()->after('instruksi_lapangan');
            $table->string('hasil_inspeksi_lapangan', 50)->nullable()->after('catatan_inspeksi_petugas');
            $table->timestamp('inspeksi_at')->nullable()->after('hasil_inspeksi_lapangan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('environmental_reports', function (Blueprint $table) {
            $table->dropColumn(['catatan_inspeksi_petugas', 'hasil_inspeksi_lapangan', 'inspeksi_at']);
        });
    }
};
