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
        Schema::create('education_articles', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->string('kategori', 50)->default('organik')->index();
            $table->text('ringkasan')->nullable();
            $table->longText('isi');
            $table->string('penulis_nama')->default('Tim Penyuluh DLH Madiun');
            $table->string('penulis_jabatan')->nullable();
            $table->unsignedSmallInteger('waktu_baca_menit')->default(4);
            $table->string('cover_image')->nullable();
            $table->string('status', 30)->default('published')->index();
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();

            $table->index(['status', 'kategori', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('education_articles');
    }
};
