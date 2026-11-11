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
        Schema::table('broadcast_announcements', function (Blueprint $table) {
            $table->string('status', 30)->default('published')->after('priority')->index();
            $table->string('kategori', 50)->default('informasi')->after('status');
            $table->string('summary', 500)->nullable()->after('title');
            $table->boolean('show_on_portal')->default(true)->after('is_active');
        });

        Schema::table('pengelola_tps3r_profiles', function (Blueprint $table) {
            $table->string('jenis_fasilitas', 30)->default('tps3r')->after('kode_tps3r')->index();
            $table->string('jam_operasional')->nullable()->after('kecamatan');
            $table->json('layanan_tersedia')->nullable()->after('jam_operasional');
            $table->json('kategori_sampah_diterima')->nullable()->after('layanan_tersedia');
            $table->string('fokus_pengolahan')->nullable()->after('kategori_sampah_diterima');
            $table->string('cakupan_wilayah')->nullable()->after('fokus_pengolahan');
            $table->string('nama_koordinator')->nullable()->after('cakupan_wilayah');
            $table->string('foto_fasilitas')->nullable()->after('nama_koordinator');
            $table->boolean('is_featured_portal')->default(false)->after('foto_fasilitas');
        });

        Schema::table('waste_products', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('is_available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('waste_products', function (Blueprint $table) {
            $table->dropColumn('is_featured');
        });

        Schema::table('pengelola_tps3r_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'jenis_fasilitas',
                'jam_operasional',
                'layanan_tersedia',
                'kategori_sampah_diterima',
                'fokus_pengolahan',
                'cakupan_wilayah',
                'nama_koordinator',
                'foto_fasilitas',
                'is_featured_portal',
            ]);
        });

        Schema::table('broadcast_announcements', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'kategori', 'summary', 'show_on_portal']);
        });
    }
};
