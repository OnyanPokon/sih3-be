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
        Schema::create('wilayah_sungai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained('kategori')->cascadeOnDelete();
            $table->foreignId('unor_id')->constrained('unor')->cascadeOnDelete();
            $table->string('kewenangan');
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->string('thumbnail')->default('default.png');
            $table->string('luas_wilayah');
            $table->integer('total_titik_pantau');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wilayah_sungai');
    }
};
