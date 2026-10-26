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
        Schema::table('complaints', function (Blueprint $table) {
            $table->foreignId('assigned_petugas_id')->nullable()->after('pelapor_id')->constrained('users')->cascadeOnDelete();
            $table->text('instruksi_admin')->nullable()->after('status');
            $table->timestamp('assigned_at')->nullable()->after('instruksi_admin');
        });

        Schema::table('environmental_reports', function (Blueprint $table) {
            $table->foreignId('assigned_petugas_id')->nullable()->after('pemrakarsa_id')->constrained('users')->cascadeOnDelete();
            $table->text('instruksi_lapangan')->nullable()->after('catatan_verifikasi');
            $table->timestamp('assigned_at')->nullable()->after('instruksi_lapangan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropForeign(['assigned_petugas_id']);
            $table->dropColumn(['assigned_petugas_id', 'instruksi_admin', 'assigned_at']);
        });

        Schema::table('environmental_reports', function (Blueprint $table) {
            $table->dropForeign(['assigned_petugas_id']);
            $table->dropColumn(['assigned_petugas_id', 'instruksi_lapangan', 'assigned_at']);
        });
    }
};
