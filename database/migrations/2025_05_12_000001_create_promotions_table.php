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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name_en');
            $table->string('name_ar');
            $table->string('code')->unique()->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('type');  // Will use PHP enum: percentage, fixed, free_shipping, buy_x_get_y
            $table->decimal('value', 10, 2)->nullable();  // For percentage/fixed discounts
            $table->decimal('min_order_value', 10, 2)->nullable();  // Minimum order value to apply
            $table->integer('usage_limit')->nullable();  // Number of times this promotion can be used
            $table->integer('usage_count')->default(0);  // Number of times this promotion has been used
            $table->boolean('is_active')->default(true);
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
        Schema::dropIfExists('promotions');
    }
};
