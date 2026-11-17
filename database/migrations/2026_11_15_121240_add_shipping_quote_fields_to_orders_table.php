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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_source')->nullable()->after('biaya_pengiriman')->index();
            $table->string('destination_kelurahan')->nullable()->after('alamat_pengiriman');
            $table->string('destination_kecamatan')->nullable()->after('destination_kelurahan');
            $table->unsignedBigInteger('rajaongkir_destination_id')->nullable()->after('destination_kecamatan');
            $table->string('shipping_courier', 50)->nullable()->after('rajaongkir_destination_id');
            $table->string('shipping_service', 100)->nullable()->after('shipping_courier');
            $table->string('shipping_etd', 100)->nullable()->after('shipping_service');
            $table->unsignedInteger('shipping_weight_grams')->default(0)->after('shipping_etd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['shipping_source']);
            $table->dropColumn([
                'shipping_source',
                'destination_kelurahan',
                'destination_kecamatan',
                'rajaongkir_destination_id',
                'shipping_courier',
                'shipping_service',
                'shipping_etd',
                'shipping_weight_grams',
            ]);
        });
    }
};
