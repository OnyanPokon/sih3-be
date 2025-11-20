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
        Schema::create('pos_pengamatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_pos_id')->constrained('jenis_pos')->onDelete('cascade');
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
        Schema::dropIfExists('pos_pengamatan');
    }
};
