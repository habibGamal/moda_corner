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
        Schema::create('direct_promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name_en')->nullable();
            $table->string('name_ar');
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->enum('type', ['price_discount', 'free_shipping']); // Two main types

            // For price discount promotions
            $table->decimal('discount_percentage', 5, 2)->nullable(); // e.g., 15.50 for 15.5%
            $table->enum('apply_to', ['all_products', 'category', 'brand'])->nullable(); // What to apply discount to
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete(); // If apply_to = category
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete(); // If apply_to = brand

            // For free shipping promotions
            $table->decimal('minimum_order_amount', 10, 2)->nullable(); // Minimum order for free shipping

            $table->boolean('is_active')->default(false);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('direct_promotions');
    }
};
