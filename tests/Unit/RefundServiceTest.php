<?php<?php



use App\DTOs\RefundRequestData;use App\DTOs\RefundRequestData;

use App\Enums\PaymentMethod;use App\Enums\PaymentMethod;

use App\Enums\PaymentStatus;use App\Enums\PaymentStatus;

use App\Models\Order;use App\Models\Order;

use App\Models\User;use App\Models\User;

use App\Services\Payment\PaymentProcessor;use App\Services\Payment\PaymentProcessor;

use App\Services\RefundService;use App\Services\RefundService;

use Illuminate\Foundation\Testing\RefreshDatabase;use Illuminate\Foundation\Testing\RefreshDatabase;

use Illuminate\Support\Facades\Config;use Illuminate\Support\Facades\Config;

use Illuminate\Support\Facades\Http;use Illuminate\Support\Facades\Http;



uses(RefreshDatabase::class);uses(RefreshDatabase::class);



beforeEach(function () {beforeEach(function () {

    $this->user = User::factory()->create();    $this->user = User::factory()->create();

    $this->refundService = app(RefundService::class);    $this->refundService = app(RefundService::class);



    // Set up test configuration    // Set up test configuration

    Config::set('services.kashier', [    Config::set('services.kashier', [

        'merchant_id' => 'MID-12345',        'merchant_id' => 'MID-12345',

        'api_key' => 'test-api-key-12345',        'api_key' => 'test-api-key-12345',

        'secret_key' => 'test-secret-key-12345',        'secret_key' => 'test-secret-key-12345',

        'mode' => 'test',        'mode' => 'test',

    ]);    ]);



    $this->paymentProcessor = app(PaymentProcessor::class);    $this->paymentProcessor = app(PaymentProcessor::class);

});});



describe('Refund Processing', function () {describe('Refund Processing', function () {

    it('processes online payment refund successfully', function () {

        $order = Order::factory()->create([            'payment_method' => PaymentMethod::CREDIT_CARD,        $order = Order::factory()->create([

            'user_id' => $this->user->id,

            'total' => 150.00,            'payment_status' => PaymentStatus::PAID,            'user_id' => $this->user->id,

            'payment_method' => PaymentMethod::CREDIT_CARD,

            'payment_status' => PaymentStatus::PAID,            'payment_id' => 'kashier-payment-123',            'total' => 150.00,

            'payment_id' => 'TX-12345',

        ]);        ]);            'payment_method' => PaymentMethod::CREDIT_CARD,



        Http::fake([            'payment_status' => PaymentStatus::PAID,

            'https://test-fep.kashier.io/v3/orders/*' => Http::response([

                'status' => 'SUCCESS',        Http::fake([            'payment_id' => 'kashier-payment-123',

                'messageEn' => 'Refund processed successfully',

                'response' => [            'https://test-fep.kashier.io/v3/orders/*' => Http::response([        ]);

                    'refundId' => 'RF-67890',

                    'amount' => '150.00',                'status' => 'SUCCESS',

                    'currency' => 'EGP',

                ]                'response' => [        Http::fake([

            ], 200)

        ]);                    'status' => 'REFUNDED',            'https://test-fep.kashier.io/v3/orders/*' => Http::response([



        $refundRequestData = RefundRequestData::fromOrder($order);                    'transactionId' => 'TX-69827599',                'status' => 'SUCCESS',

        $result = $this->refundService->processRefund($order, $refundRequestData);

                    'gatewayCode' => 'APPROVED',                'response' => [

        expect($result)->toBeTrue();

                            'cardOrderId' => 'card-order-123',                    'status' => 'REFUNDED',

        $order->refresh();

        expect($order->payment_status)->toBe(PaymentStatus::REFUNDED);                    'orderReference' => 'TEST-ORD-37089',                    'transactionId' => 'TX-69827599',

    });

                ],                    'gatewayCode' => 'APPROVED',

    it('returns false for cash on delivery orders', function () {

        $order = Order::factory()->create([                'messages' => [                    'cardOrderId' => 'card-order-123',

            'user_id' => $this->user->id,

            'payment_method' => PaymentMethod::CASH_ON_DELIVERY,                    [                    'orderReference' => 'TEST-ORD-37089',

            'payment_status' => PaymentStatus::PAID,

        ]);                        'code' => 'APPROVED',                ],



        $refundRequestData = RefundRequestData::fromOrder($order);                        'en' => 'Transaction approved successfully',                'messages' => [

        $result = $this->refundService->processRefund($order, $refundRequestData);

                        'ar' => 'تم الموافقة على المعاملة بنجاح',                    'en' => 'Refund successful',

        expect($result)->toBeFalse();

    });                    ],                ],



    it('handles unpaid orders gracefully', function () {                ],            ], 200),

        $order = Order::factory()->create([

            'user_id' => $this->user->id,                'success' => true,        ]);

            'payment_method' => PaymentMethod::CREDIT_CARD,

            'payment_status' => PaymentStatus::PENDING,            ], 200),

        ]);

        ]);        $result = $this->refundService->processRefund($order);

        $refundRequestData = RefundRequestData::fromOrder($order);

        $result = $this->refundService->processRefund($order, $refundRequestData);



        expect($result)->toBeFalse();        $refundRequest = new RefundRequestData(        expect($result)->toBeTrue();

    });

});            orderId: $order->id,    });

            amount: 50.00,

            reason: 'Customer request'    it('processes COD order without refund API call', function () {

        );        $order = Order::factory()->create([

            'user_id' => $this->user->id,

        $result = $this->refundService->processRefund($refundRequest);            'total' => 150.00,

            'payment_method' => PaymentMethod::CASH_ON_DELIVERY,

        expect($result->success)->toBeTrue();            'payment_status' => PaymentStatus::PENDING,

        expect($result->refundId)->not->toBeNull();        ]);

        expect($result->gatewayResponse)->toHaveKey('transactionId');

    });        // Should not make any HTTP calls for COD

        Http::fake();

    it('constructs correct payment gateway order ID', function () {

        $order = Order::factory()->create([        $result = $this->refundService->processRefund($order);

            'user_id' => $this->user->id,

            'payment_method' => PaymentMethod::CREDIT_CARD,        expect($result)->toBeTrue();

            'payment_status' => PaymentStatus::PAID,        Http::assertNothingSent();

            'payment_id' => 'kashier-payment-123',    });

            'total' => 100.00,

        ]);    it('constructs correct payment gateway order ID', function () {

        Http::fake([

        Http::fake([            'https://test-fep.kashier.io/v3/orders/*' => Http::response([

            'https://test-fep.kashier.io/v3/orders/*' => Http::response([                'status' => 'success',

                'status' => 'SUCCESS',                'refundId' => 'refund-123',

                'response' => [            ], 200),

                    'status' => 'REFUNDED',        ]);

                    'transactionId' => 'TX-12345',

                ],        $order = Order::factory()->create([

                'success' => true,            'user_id' => $this->user->id,

            ], 200),            'payment_method' => PaymentMethod::CREDIT_CARD,

        ]);            'payment_status' => PaymentStatus::PAID,

            'payment_id' => 'kashier-payment-123',

        $refundRequest = new RefundRequestData(            'total' => 100.00,

            orderId: $order->id,        ]););

            amount: 25.00,

            reason: 'Test refund'    it('throws exception when Kashier refund fails', function () {

        );        $order = Order::factory()->create([

            'user_id' => $this->user->id,

        $this->refundService->processRefund($refundRequest);            'total' => 150.00,

            'payment_method' => PaymentMethod::KASHIER,

        Http::assertSent(function ($request) use ($order) {            'payment_status' => PaymentStatus::PAID,

            return str_contains($request->url(), "Larament-{$order->id}");            'payment_id' => 'kashier-payment-123',

        });        ]);

    });

        Http::fake([

    it('throws exception when payment gateway refund fails', function () {            'https://test-fep.kashier.io/v3/orders/*' => Http::response([

        $order = Order::factory()->create([                'status' => 'FAILED',

            'user_id' => $this->user->id,                'response' => [

            'payment_method' => PaymentMethod::CREDIT_CARD,                    'status' => 'FAILED',

            'payment_status' => PaymentStatus::PAID,                    'gatewayCode' => 'DECLINED',

            'payment_id' => 'kashier-payment-123',                ],

            'total' => 100.00,                'messages' => [

        ]);                    'en' => 'Refund declined by gateway',

                ],

        Http::fake([            ], 400),

            'https://test-fep.kashier.io/v3/orders/*' => Http::response([        ]);

                'status' => 'FAILED',

                'messages' => [        expect(fn () => $this->refundService->processRefund($order))

                    [            ->toThrow(Exception::class, 'Payment refund failed: Refund declined by gateway');

                        'code' => 'REFUND_FAILED',    });

                        'en' => 'Refund failed due to insufficient funds',});

                        'ar' => 'فشل في الاسترداد بسبب عدم كفاية الأموال',

                    ],describe('Refund Eligibility Check', function () {

                ],    it('returns false for COD orders', function () {

                'success' => false,        $order = Order::factory()->create([

            ], 400),            'payment_method' => PaymentMethod::CASH_ON_DELIVERY,

        ]);            'payment_status' => PaymentStatus::PENDING,

        ]);

        $refundRequest = new RefundRequestData(

            orderId: $order->id,        $canRefund = $this->refundService->canProcessRefund($order);

            amount: 50.00,

            reason: 'Customer request'        expect($canRefund)->toBeFalse();

        );    });



        expect(fn () => $this->refundService->processRefund($refundRequest))    it('returns false for unpaid orders', function () {

            ->toThrow(\Exception::class, 'Payment refund failed');        $order = Order::factory()->create([

    });            'payment_method' => PaymentMethod::KASHIER,

});            'payment_status' => PaymentStatus::PENDING,

        ]);

describe('Refund Eligibility Check', function () {

    it('returns false for online orders without payment ID', function () {        $canRefund = $this->refundService->canProcessRefund($order);

        $order = Order::factory()->create([

            'user_id' => $this->user->id,        expect($canRefund)->toBeFalse();

            'payment_method' => PaymentMethod::CREDIT_CARD,    });

            'payment_status' => PaymentStatus::PENDING,

            'payment_id' => null,    it('returns false for online orders without payment ID', function () {

        ]);        $order = Order::factory()->create([

            'payment_method' => PaymentMethod::KASHIER,

        $result = $this->refundService->isRefundEligible($order);            'payment_status' => PaymentStatus::PAID,

            'payment_id' => null,

        expect($result)->toBeFalse();        ]);

    });

        $canRefund = $this->refundService->canProcessRefund($order);

    it('returns false for cash on delivery orders', function () {

        $order = Order::factory()->create([        expect($canRefund)->toBeFalse();

            'user_id' => $this->user->id,    });

            'payment_method' => PaymentMethod::CASH_ON_DELIVERY,

            'payment_status' => PaymentStatus::PENDING,    it('returns true for eligible Kashier orders', function () {

        ]);        $order = Order::factory()->create([

            'payment_method' => PaymentMethod::KASHIER,

        $result = $this->refundService->isRefundEligible($order);            'payment_status' => PaymentStatus::PAID,

            'payment_id' => 'kashier-payment-123',

        expect($result)->toBeFalse();        ]);

    });

        $canRefund = $this->refundService->canProcessRefund($order);

    it('returns true for eligible online payment orders', function () {

        $order = Order::factory()->create([        expect($canRefund)->toBeTrue();

            'user_id' => $this->user->id,    });

            'payment_method' => PaymentMethod::CREDIT_CARD,});

            'payment_status' => PaymentStatus::PAID,
            'payment_id' => 'kashier-payment-123',
        ]);

        $result = $this->refundService->isRefundEligible($order);

        expect($result)->toBeTrue();
    });
});
