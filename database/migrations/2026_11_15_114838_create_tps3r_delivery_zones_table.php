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
        Schema::create('tps3r_delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('pengelola_tps3r_profile_id')
                ->constrained('pengelola_tps3r_profiles')
                ->cascadeOnDelete();
            $table->string('kelurahan');
            $table->string('kecamatan');
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->string('status')->default('pending')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('rejection_reason', 500)->nullable();
            $table->timestamps();

            $table->unique(
                ['pengelola_tps3r_profile_id', 'kelurahan', 'kecamatan'],
                'tps3r_delivery_zone_unique',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tps3r_delivery_zones');
    }
};
