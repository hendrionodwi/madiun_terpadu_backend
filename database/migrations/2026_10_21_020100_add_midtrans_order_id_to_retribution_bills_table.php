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
        Schema::table('retribution_bills', function (Blueprint $table) {
            $table->string('midtrans_order_id', 80)
                ->nullable()
                ->unique()
                ->after('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retribution_bills', function (Blueprint $table) {
            $table->dropColumn('midtrans_order_id');
        });
    }
};
