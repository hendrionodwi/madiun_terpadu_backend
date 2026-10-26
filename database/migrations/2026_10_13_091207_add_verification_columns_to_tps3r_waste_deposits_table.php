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
        Schema::table('tps3r_waste_deposits', function (Blueprint $table) {
            $table->string('status', 30)->default('diterima')->after('berat_kg')->index();
            $table->decimal('berat_konfirmasi_kg', 10, 2)->nullable()->after('status');
            $table->timestamp('verified_at')->nullable()->after('berat_konfirmasi_kg');
            $table->text('catatan_verifikasi')->nullable()->after('verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tps3r_waste_deposits', function (Blueprint $table) {
            $table->dropColumn(['status', 'berat_konfirmasi_kg', 'verified_at', 'catatan_verifikasi']);
        });
    }
};
