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
            $table->string('midtrans_merchant_id')->nullable()->after('longitude');
            $table->string('midtrans_client_key')->nullable()->after('midtrans_merchant_id');
            $table->text('midtrans_server_key')->nullable()->after('midtrans_client_key');
            $table->boolean('midtrans_is_production')->default(false)->after('midtrans_server_key');
            $table->boolean('midtrans_is_active')->default(false)->after('midtrans_is_production');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengelola_tps3r_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'midtrans_merchant_id',
                'midtrans_client_key',
                'midtrans_server_key',
                'midtrans_is_production',
                'midtrans_is_active',
            ]);
        });
    }
};
