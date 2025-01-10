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
        Schema::create('a_b_c_s', function (Blueprint $table) {
            $table->id();
            $table->text('name')->nullable();
            $table->string('country')->nullable();
            $table->string('exchange')->nullable();
            $table->string('currency')->nullable();
            $table->string('type')->nullable();
            $table->string('isin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('a_b_c_s');
    }
};
