<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('order_status')->default(OrderStatus::PROCESSING->value);
            $table->foreignId('shipping_address_id')->constrained('addresses');
            $table->text('notes')->nullable();

            $table->string('payment_id')->nullable();
            $table->text('payment_details')->nullable();
            $table->string('payment_status')->default(PaymentStatus::PENDING->value);
            $table->string('payment_method')->default(PaymentMethod::CASH_ON_DELIVERY->value);

            $table->string('return_status')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('return_requested_at')->nullable();
            $table->text('return_reason')->nullable();

            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('refunded_at')->nullable();

            $table->string('coupon_code')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('shipping_cost', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
