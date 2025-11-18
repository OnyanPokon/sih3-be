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
        Schema::create('detail_pengamatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_pengamatan_id')->constrained('pos_pengamatan')->onDelete('cascade');
            $table->string('nama');
            $table->string('koordinat');
            $table->string('alamat');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pengamatan');
    }
};
