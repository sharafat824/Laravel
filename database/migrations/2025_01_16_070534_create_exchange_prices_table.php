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
        Schema::create('exchange_prices', function (Blueprint $table) {
            $table->id();
            $table->decimal('adjusted_close', 10, 4); // Decimal for monetary values
            $table->decimal('close', 10, 4); // Decimal for monetary values
            $table->date('date'); // Date type for the date field
            $table->decimal('high', 10, 4); // Decimal for monetary values
            $table->decimal('low', 10, 4); // Decimal for monetary values
            $table->decimal('open', 10, 4); // Decimal for monetary values
            $table->integer('volume'); // Integer for volume
            $table->timestamps(); // Adds created_at and updated_at colum
            $table->foreignId('exchange_id')->constrained('exchanges')->onDelete('cascade'); // Foreign key referencing exchanges table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_prices');
    }
};
