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
        Schema::table('route_assignments', function (Blueprint $table) {
            $table->string('start_location', 150)->nullable()->after('route_name')->comment('Depo/Titik Awal Keberangkatan');
            $table->string('end_facility', 150)->nullable()->after('start_location')->comment('Fasilitas Tujuan Akhir (TPS3R/TPA)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('route_assignments', function (Blueprint $table) {
            $table->dropColumn(['start_location', 'end_facility']);
        });
    }
};
