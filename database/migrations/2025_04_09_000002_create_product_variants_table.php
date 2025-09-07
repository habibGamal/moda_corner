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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->json('images')->nullable(); // Store multiple images as JSON array
            $table->integer('quantity')->default(0);
            $table->decimal('price', 10, 2)->nullable(); // Optional override of product price
            $table->decimal('sale_price', 10, 2)->nullable(); // Optional override of product sale price
            $table->string('color')->nullable(); // Optional color attribute
            $table->string('size')->nullable(); // Optional size attribute
            $table->string('capacity')->nullable(); // Optional capacity attribute
            $table->json('additional_attributes')->nullable(); // For future extensibility
            $table->boolean('is_default')->default(false); // To mark the default variant
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
