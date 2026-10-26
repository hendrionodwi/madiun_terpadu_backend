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
            $table->string('route_code', 50)->nullable()->after('nomor_resi');
        });

        Schema::table('waste_weighings', function (Blueprint $table) {
            $table->foreignId('tps3r_waste_deposit_id')
                ->nullable()
                ->after('pickup_schedule_id')
                ->constrained('tps3r_waste_deposits')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('waste_weighings', function (Blueprint $table) {
            $table->dropForeign(['tps3r_waste_deposit_id']);
            $table->dropColumn('tps3r_waste_deposit_id');
        });

        Schema::table('tps3r_waste_deposits', function (Blueprint $table) {
            $table->dropColumn('route_code');
        });
    }
};
