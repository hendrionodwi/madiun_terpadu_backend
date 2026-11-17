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
        Schema::table('waste_products', function (Blueprint $table) {
            $table->unsignedInteger('weight_grams')->default(1000)->after('stok');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('waste_products', function (Blueprint $table) {
            $table->dropColumn('weight_grams');
        });
    }
};
