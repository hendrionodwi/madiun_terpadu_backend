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
            $table->text('midtrans_signature_secret')->nullable()->after('midtrans_server_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengelola_tps3r_profiles', function (Blueprint $table) {
            $table->dropColumn('midtrans_signature_secret');
        });
    }
};
