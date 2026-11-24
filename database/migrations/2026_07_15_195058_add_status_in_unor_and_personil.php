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
        Schema::table('unor', function (Blueprint $table) {
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->after('no_hp');
        });
        Schema::table('personil', function (Blueprint $table) {
            $table->foreignId('unor_id')->nullable()->after('status')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unor', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('personil', function (Blueprint $table) {
            $table->dropForeign(['unor_id']);
            $table->foreignId('unor_id')->nullable()->after('status')->change();
        });
    }
};
