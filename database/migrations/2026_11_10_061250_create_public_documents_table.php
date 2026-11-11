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
        Schema::create('public_documents', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->string('kategori', 50)->default('panduan')->index();
            $table->text('deskripsi')->nullable();
            $table->string('file_path');
            $table->string('file_format', 20)->default('PDF');
            $table->unsignedBigInteger('file_size_bytes')->nullable();
            $table->date('tanggal_rilis')->nullable();
            $table->string('hak_akses_label', 100)->default('Akses Bebas Publik');
            $table->string('status', 30)->default('published')->index();
            $table->unsignedInteger('download_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'kategori']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_documents');
    }
};
