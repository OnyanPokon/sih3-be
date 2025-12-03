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
        Schema::create('pemetaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained('kategori')->onDelete('cascade');
            $table->foreignId('unor_id')->constrained('unor')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nama_pemetaan');
            $table->text('deskripsi')->nullable();
            $table->enum('tipe', ['upload', 'manual']);
            $table->enum('jenis', ['point', 'polygon', 'polyline'])->nullable();
            $table->string('file')->nullable();
            $table->json('koordinat')->nullable();
            $table->enum('status', ['publikasi', 'privasi'])->default('publikasi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemetaan');
    }
};
