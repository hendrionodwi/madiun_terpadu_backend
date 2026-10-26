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
        Schema::create('waste_processing_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengelola_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tps3r_profile_id')->constrained('pengelola_tps3r_profiles')->cascadeOnDelete();
            $table->foreignId('tps3r_waste_deposit_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('process_method');
            $table->string('waste_type')->index();
            $table->decimal('input_weight_kg', 10, 2);
            $table->decimal('output_weight_kg', 10, 2)->nullable();
            $table->string('status')->default('planned')->index();
            $table->date('started_on');
            $table->date('completed_on')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['pengelola_id', 'started_on']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_processing_records');
    }
};
