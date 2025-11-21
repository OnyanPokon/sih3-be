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
        Schema::create('koordinat_telemetri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_pengamatan_id')->constrained('pos_pengamatan')->onDelete('cascade');
            $table->float('nilai');
            $table->time('waktu');
            $table->date('tanggal');
            $table->string('warna')->default('biru');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('koordinat_telemetri');
    }
};
