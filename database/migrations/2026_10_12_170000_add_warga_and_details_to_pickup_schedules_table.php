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
        Schema::table('pickup_schedules', function (Blueprint $table) {
            $table->foreignId('warga_id')
                ->nullable()
                ->after('petugas_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('jenis_sampah')->nullable()->after('nama_wilayah_rute');
            $table->text('alamat_penjemputan')->nullable()->after('keterangan');
            $table->index(['warga_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pickup_schedules', function (Blueprint $table) {
            $table->dropForeign(['warga_id']);
            $table->dropIndex(['warga_id', 'status']);
            $table->dropColumn(['warga_id', 'jenis_sampah', 'alamat_penjemputan']);
        });
    }
};
