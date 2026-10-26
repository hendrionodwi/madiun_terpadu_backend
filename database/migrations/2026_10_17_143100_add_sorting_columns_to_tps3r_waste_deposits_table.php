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
            $table->decimal('berat_organik_pilah_kg', 10, 2)->nullable()->after('berat_konfirmasi_kg');
            $table->decimal('berat_anorganik_pilah_kg', 10, 2)->nullable()->after('berat_organik_pilah_kg');
            $table->decimal('berat_residu_pilah_kg', 10, 2)->nullable()->after('berat_anorganik_pilah_kg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tps3r_waste_deposits', function (Blueprint $table) {
            $table->dropColumn([
                'berat_organik_pilah_kg',
                'berat_anorganik_pilah_kg',
                'berat_residu_pilah_kg',
            ]);
        });
    }
};
