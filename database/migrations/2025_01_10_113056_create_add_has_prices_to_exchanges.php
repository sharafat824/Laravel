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
        Schema::table('exchanges', function (Blueprint $table) {
            $table->enum('price_status', ['pending', 'processing', 'done'])->default('pending');
            $table->dateTime('price_created_at')->default('2025-01-01 00:00:00');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exchanges', function (Blueprint $table) {
            $table->dropColumn('price_status');
            $table->dropColumn('price_created_at');
        });
    }
};
