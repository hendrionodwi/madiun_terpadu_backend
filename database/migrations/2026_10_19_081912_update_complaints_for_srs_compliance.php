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
            $table->string('kategori_masalah', 100)->default('sampah_liar')->after('judul');
            $table->boolean('is_anonim')->default(false)->after('foto');
        });

        Schema::table('complaint_verifications', function (Blueprint $table) {
            $table->text('rekomendasi_tindak_lanjut')->nullable()->after('catatan_petugas');
        });

        Schema::create('complaint_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained('complaints')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 50)->index();
            $table->string('judul_notifikasi');
            $table->text('pesan');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_status_logs');

        Schema::table('complaint_verifications', function (Blueprint $table) {
            $table->dropColumn('rekomendasi_tindak_lanjut');
        });

        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn(['kategori_masalah', 'is_anonim']);
        });
    }
};
