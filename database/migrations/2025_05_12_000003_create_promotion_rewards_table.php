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
        Schema::create('promotion_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->onDelete('cascade');
            $table->string('type');  // Will use PHP enum: product, category, brand
            $table->unsignedBigInteger('entity_id')->nullable();  // ID of the product/category/brand
            $table->integer('quantity')->default(1);  // How many items to give for free or at discount
            $table->decimal('discount_percentage', 5, 2)->nullable();  // Percentage discount on the reward item
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_rewards');
    }
};
