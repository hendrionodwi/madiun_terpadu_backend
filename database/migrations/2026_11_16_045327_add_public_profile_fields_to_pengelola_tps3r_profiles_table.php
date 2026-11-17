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
        Schema::table('pengelola_tps3r_profiles', function (Blueprint $table) {
            $table->text('deskripsi')->nullable()->after('jenis_fasilitas');
            $table->string('kontak_publik', 100)->nullable()->after('nama_koordinator');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengelola_tps3r_profiles', function (Blueprint $table) {
            $table->dropColumn(['deskripsi', 'kontak_publik']);
        });
    }
};
