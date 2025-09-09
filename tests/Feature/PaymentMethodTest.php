<?php

namespace Tests\Feature;

use App\DTOs\Payment\CardPaymentData;
use App\DTOs\Payment\WalletPaymentData;
use App\Models\User;
use App\Models\Address;
use App\Models\Order;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\Payment\Gateways\KashierGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Address $address;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->address = Address::factory()->create(['user_id' => $this->user->id]);
        $this->product = Product::factory()->create(['price' => 100.00]);
    }

    public function test_card_payment_data_validation(): void
    {
        $validCardData = CardPaymentData::fromRequestArray([
            'card_number' => '5123456789012346',
            'expiry_month' => '05',
            'expiry_year' => '26',
            'security_code' => '100',
            'name_on_card' => 'Test User',
        ]);

        $this->assertTrue($validCardData->isValid());
        $this->assertEmpty($validCardData->validate());

        $invalidCardData = CardPaymentData::fromRequestArray([
            'card_number' => '',
            'expiry_month' => '13',
            'expiry_year' => '20',
            'security_code' => '12',
            'name_on_card' => '',
        ]);

        $this->assertFalse($invalidCardData->isValid());
        $this->assertNotEmpty($invalidCardData->validate());
    }

    public function test_wallet_payment_data_validation(): void
    {
        $validWalletData = WalletPaymentData::fromRequestArray([
            'mobile_phone' => '01001234567',
        ]);

        $this->assertTrue($validWalletData->isValid());
        $this->assertEmpty($validWalletData->validate());

        $invalidWalletData = WalletPaymentData::fromRequestArray([
            'mobile_phone' => '1234567890',
        ]);

        $this->assertFalse($invalidWalletData->isValid());
        $this->assertNotEmpty($invalidWalletData->validate());
    }

    public function test_checkout_with_card_payment_method(): void
    {
        $this->actingAs($this->user);

        // Add product to cart
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->post(route('orders.store'), [
            'address_id' => $this->address->id,
            'payment_method' => 'card',
            'card_number' => '5123456789012346',
            'expiry_month' => '05',
            'expiry_year' => '26',
            'security_code' => '100',
            'name_on_card' => 'Test User',
            'notes' => 'Test order with card payment',
        ]);

        // Should redirect to order show page or handle payment processing
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'address_id' => $this->address->id,
        ]);
    }

    public function test_checkout_with_wallet_payment_method(): void
    {
        $this->actingAs($this->user);

        // Add product to cart
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response = $this->post(route('orders.store'), [
            'address_id' => $this->address->id,
            'payment_method' => 'wallet',
            'mobile_phone' => '01001234567',
            'notes' => 'Test order with wallet payment',
        ]);

        // Should redirect to order show page or handle payment processing
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'address_id' => $this->address->id,
        ]);
    }

    public function test_checkout_form_validation_for_card_payment(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('orders.store'), [
            'address_id' => $this->address->id,
            'payment_method' => 'card',
            // Missing card details
        ]);

        $response->assertSessionHasErrors(['card_number', 'expiry_month', 'expiry_year', 'security_code', 'name_on_card']);
    }

    public function test_checkout_form_validation_for_wallet_payment(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('orders.store'), [
            'address_id' => $this->address->id,
            'payment_method' => 'wallet',
            'mobile_phone' => 'invalid_phone',
        ]);

        $response->assertSessionHasErrors(['mobile_phone']);
    }

    public function test_kashier_gateway_supports_new_payment_methods(): void
    {
        $gateway = new KashierGateway();

        $this->assertTrue($gateway->supportsPaymentMethod('card'));
        $this->assertTrue($gateway->supportsPaymentMethod('wallet'));
        $this->assertTrue($gateway->supportsPaymentMethod('credit_card'));
        $this->assertFalse($gateway->supportsPaymentMethod('paypal'));
    }
}
