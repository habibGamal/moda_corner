<?php

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use App\Models\Address;
use App\Models\Area;
use App\Models\Gov;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Set up test Kashier configuration
    Config::set('services.kashier', [
        'merchant_id' => 'MID-12345',
        'api_key' => 'test-api-key-12345',
        'secret_key' => 'test-secret-key-12345',
        'mode' => 'test',
    ]);

    // Create address structure for orders
    $gov = Gov::factory()->create();
    $area = Area::factory()->create(['gov_id' => $gov->id]);
    $this->address = Address::factory()->create([
        'user_id' => $this->user->id,
        'area_id' => $area->id,
    ]);
});

describe('initiatePayment', function () {    it('shows payment page for unpaid kashier order', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_method' => PaymentMethod::KASHIER,
            'payment_status' => PaymentStatus::PENDING,
            'total' => 150.00,
            'shipping_address_id' => $this->address->id,
        ]);

        $response = $this->get(route('kashier.payment.initiate', [
            'order_id' => $order->id
        ]));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Payments/Kashier')
            ->has('kashierParams')
            ->has('order')
            ->where('order.id', $order->id)
            ->where('kashierParams.merchantId', 'MID-12345')
            ->where('kashierParams.orderId', (string) $order->id)
            ->where('kashierParams.amount', '150.00')
            ->where('kashierParams.currency', 'EGP')
            ->where('kashierParams.mode', 'test')
        );

        // Check that order ID is stored in session
        expect(session('kashier_order_id'))->toBe($order->id);
    });    it('redirects when order is already paid', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_method' => PaymentMethod::KASHIER,
            'payment_status' => PaymentStatus::PAID,
            'shipping_address_id' => $this->address->id,
        ]);

        $response = $this->get(route('kashier.payment.initiate', [
            'order_id' => $order->id
        ]));

        $response->assertRedirect(route('orders.show', $order->id))
                ->assertSessionHas('info', 'This order has already been paid.');
    });    it('redirects when order does not use kashier payment', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_method' => PaymentMethod::CASH_ON_DELIVERY,
            'payment_status' => PaymentStatus::PENDING,
            'shipping_address_id' => $this->address->id,
        ]);

        $response = $this->get(route('kashier.payment.initiate', [
            'order_id' => $order->id
        ]));

        $response->assertRedirect(route('orders.show', $order->id))
                ->assertSessionHas('error', 'This order does not use online payment.');
    });    it('redirects to orders when order not found', function () {
        $response = $this->get(route('kashier.payment.initiate', [
            'order_id' => 999999
        ]));

        $response->assertRedirect(route('orders.index'))
                ->assertSessionHas('error');
    });
});

describe('showPayment', function () {
    it('shows payment page for valid order', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_method' => PaymentMethod::KASHIER,
            'payment_status' => PaymentStatus::PENDING,
            'total' => 200.00,
            'shipping_address_id' => $this->address->id,
        ]);

        $response = $this->get(route('kashier.payment.show', $order->id));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Payments/Kashier')
            ->has('kashierParams')
            ->has('order')
            ->where('order.id', $order->id)
            ->where('kashierParams.merchantId', 'MID-12345')
            ->where('kashierParams.amount', '200.00')
        );

        expect(session('kashier_order_id'))->toBe($order->id);
    });

    it('redirects when order is already paid', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_method' => PaymentMethod::KASHIER,
            'payment_status' => PaymentStatus::PAID,
            'shipping_address_id' => $this->address->id,
        ]);

        $response = $this->get(route('kashier.payment.show', $order->id));

        $response->assertRedirect(route('orders.show', $order->id))
                ->assertSessionHas('info', 'This order has already been paid.');
    });

    it('redirects when order does not use kashier payment', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_method' => PaymentMethod::CREDIT_CARD,
            'payment_status' => PaymentStatus::PENDING,
            'shipping_address_id' => $this->address->id,
        ]);

        $response = $this->get(route('kashier.payment.show', $order->id));

        $response->assertRedirect(route('orders.show', $order->id))
                ->assertSessionHas('error', 'This order does not use online payment.');
    });
});

describe('handleSuccess', function () {
    it('processes successful payment with valid signature', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_method' => PaymentMethod::KASHIER,
            'payment_status' => PaymentStatus::PENDING,
            'total' => 100.00,
            'shipping_address_id' => $this->address->id,
        ]);

        // Store order ID in session as controller expects
        session(['kashier_order_id' => $order->id]);

        // Prepare valid payment response data
        $paymentData = [
            'paymentId' => 'PAY123456',
            'orderId' => (string) $order->id,
            'amount' => '100.00',
            'currency' => 'EGP',
            'status' => 'SUCCESS',
            'mode' => 'test',
        ];

        // Generate valid signature
        $queryString = 'paymentId=PAY123456&orderId=' . $order->id . '&amount=100.00&currency=EGP&status=SUCCESS';
        $validSignature = hash_hmac('sha256', $queryString, 'test-api-key-12345');
        $paymentData['signature'] = $validSignature;

        $response = $this->get(route('kashier.payment.success', $paymentData));

        $response->assertRedirect(route('orders.show', $order->id))
                ->assertSessionHas('success', 'Payment completed successfully! Your order is being processed.');

        // Verify order was updated
        $order->refresh();
        expect($order->payment_status)->toBe(PaymentStatus::PAID);
        expect($order->payment_id)->toBe('PAY123456');
        expect($order->payment_details)->toBeJson();

        // Verify session was cleared
        expect(session('kashier_order_id'))->toBeNull();
    });

    it('rejects payment with invalid signature', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_method' => PaymentMethod::KASHIER,
            'payment_status' => PaymentStatus::PENDING,
            'shipping_address_id' => $this->address->id,
        ]);

        session(['kashier_order_id' => $order->id]);

        $paymentData = [
            'paymentId' => 'PAY123456',
            'orderId' => (string) $order->id,
            'amount' => '100.00',
            'currency' => 'EGP',
            'status' => 'SUCCESS',
            'signature' => 'invalid-signature',
            'mode' => 'test',
        ];

        $response = $this->get(route('kashier.payment.success', $paymentData));

        $response->assertRedirect(route('checkout.index'))
                ->assertSessionHas('error', 'Payment verification failed. Please contact support.');

        // Verify order was not updated
        $order->refresh();
        expect($order->payment_status)->toBe(PaymentStatus::PENDING);
    });

    it('handles missing order ID in session', function () {
        $paymentData = [
            'paymentId' => 'PAY123456',
            'status' => 'SUCCESS',
            'signature' => 'some-signature',
        ];

        $response = $this->get(route('kashier.payment.success', $paymentData));

        $response->assertRedirect(route('checkout.index'))
                ->assertSessionHas('error', 'Payment information not found. Please try again.');
    });

    it('handles order not found error', function () {
        session(['kashier_order_id' => 999999]);

        $paymentData = [
            'paymentId' => 'PAY123456',
            'status' => 'SUCCESS',
            'signature' => 'some-signature',
        ];

        $response = $this->get(route('kashier.payment.success', $paymentData));

        $response->assertRedirect(route('checkout.index'))
                ->assertSessionHas('error');
    });
});

describe('handleFailure', function () {
    it('handles payment failure and updates order status', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_method' => PaymentMethod::KASHIER,
            'payment_status' => PaymentStatus::PENDING,
            'shipping_address_id' => $this->address->id,
        ]);

        session(['kashier_order_id' => $order->id]);

        $failureData = [
            'status' => 'FAILED',
            'error' => 'Payment declined',
        ];

        $response = $this->get(route('kashier.payment.failure', $failureData));

        $response->assertRedirect(route('checkout.index'))
                ->assertSessionHas('error', 'Payment was not successful. Please try again or use a different payment method.');

        // Verify order status was updated
        $order->refresh();
        expect($order->payment_status)->toBe(PaymentStatus::FAILED);

        // Verify session was cleared
        expect(session('kashier_order_id'))->toBeNull();
    });

    it('handles failure without order ID in session', function () {
        $failureData = [
            'status' => 'FAILED',
            'error' => 'Payment declined',
        ];

        $response = $this->get(route('kashier.payment.failure', $failureData));

        $response->assertRedirect(route('checkout.index'))
                ->assertSessionHas('error', 'Payment was not successful. Please try again or use a different payment method.');
    });

    it('handles failure with invalid order ID', function () {
        session(['kashier_order_id' => 999999]);

        $failureData = [
            'status' => 'FAILED',
            'error' => 'Payment declined',
        ];

        $response = $this->get(route('kashier.payment.failure', $failureData));

        $response->assertRedirect(route('checkout.index'))
                ->assertSessionHas('error', 'Payment was not successful. Please try again or use a different payment method.');

        // Verify session was cleared
        expect(session('kashier_order_id'))->toBeNull();
    });
});

describe('handleWebhook', function () {
    it('processes webhook with valid signature', function () {
        $webhookData = [
            'paymentId' => 'PAY789012',
            'merchantOrderId' => '123',
            'amount' => '150.00',
            'currency' => 'EGP',
            'status' => 'SUCCESS',
            'mode' => 'test',
        ];

        // Generate valid signature
        $queryString = 'paymentId=PAY789012&merchantOrderId=123&amount=150.00&currency=EGP&status=SUCCESS';
        $validSignature = hash_hmac('sha256', $queryString, 'test-api-key-12345');
        $webhookData['signature'] = $validSignature;

        $response = $this->post(route('kashier.payment.webhook'), $webhookData);

        $response->assertOk()
                ->assertJson(['status' => 'success']);
    });

    it('rejects webhook with invalid signature', function () {
        $webhookData = [
            'paymentId' => 'PAY789012',
            'merchantOrderId' => '123',
            'amount' => '150.00',
            'currency' => 'EGP',
            'status' => 'SUCCESS',
            'signature' => 'invalid-signature',
            'mode' => 'test',
        ];

        $response = $this->post(route('kashier.payment.webhook'), $webhookData);

        $response->assertStatus(400)
                ->assertJson(['status' => 'error', 'message' => 'Invalid signature']);
    });    it('handles webhook processing errors gracefully', function () {
        // Test with malformed data that might cause exceptions
        $webhookData = [
            'invalid' => 'data',
            'signature' => 'invalid-signature', // Include signature to avoid undefined key error
        ];

        $response = $this->post(route('kashier.payment.webhook'), $webhookData);

        $response->assertStatus(400)
                ->assertJson(['status' => 'error', 'message' => 'Invalid signature']);
    });
});

describe('Payment Flow Integration', function () {
    it('completes full payment flow from initiation to success', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_method' => PaymentMethod::KASHIER,
            'payment_status' => PaymentStatus::PENDING,
            'total' => 250.00,
            'shipping_address_id' => $this->address->id,
        ]);        // Step 1: Initiate payment
        $initiateResponse = $this->get(route('kashier.payment.initiate', [
            'order_id' => $order->id
        ]));

        $initiateResponse->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Payments/Kashier')
            ->where('order.id', $order->id)
        );

        expect(session('kashier_order_id'))->toBe($order->id);

        // Step 2: Simulate successful payment callback
        $paymentData = [
            'paymentId' => 'PAY654321',
            'orderId' => (string) $order->id,
            'amount' => '250.00',
            'currency' => 'EGP',
            'status' => 'SUCCESS',
            'mode' => 'test',
        ];

        $queryString = 'paymentId=PAY654321&orderId=' . $order->id . '&amount=250.00&currency=EGP&status=SUCCESS';
        $validSignature = hash_hmac('sha256', $queryString, 'test-api-key-12345');
        $paymentData['signature'] = $validSignature;

        $successResponse = $this->get(route('kashier.payment.success', $paymentData));

        $successResponse->assertRedirect(route('orders.show', $order->id))
                       ->assertSessionHas('success');

        // Verify final order state
        $order->refresh();
        expect($order->payment_status)->toBe(PaymentStatus::PAID);
        expect($order->payment_id)->toBe('PAY654321');
        expect(session('kashier_order_id'))->toBeNull();
    });

    it('handles payment failure flow correctly', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_method' => PaymentMethod::KASHIER,
            'payment_status' => PaymentStatus::PENDING,
            'total' => 100.00,
            'shipping_address_id' => $this->address->id,
        ]);        // Step 1: Initiate payment
        $this->get(route('kashier.payment.initiate', [
            'order_id' => $order->id
        ]));

        expect(session('kashier_order_id'))->toBe($order->id);

        // Step 2: Simulate payment failure
        $failureResponse = $this->get(route('kashier.payment.failure', [
            'status' => 'FAILED',
            'error' => 'Card declined'
        ]));

        $failureResponse->assertRedirect(route('checkout.index'))
                       ->assertSessionHas('error');

        // Verify order status was updated
        $order->refresh();
        expect($order->payment_status)->toBe(PaymentStatus::FAILED);
        expect(session('kashier_order_id'))->toBeNull();
    });
});
