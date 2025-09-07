<?php

use App\DTOs\KashierPaymentData;
use App\DTOs\RefundRequestData;
use App\DTOs\RefundResultData;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use App\Services\KashierPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->kashierService = app(KashierPaymentService::class);

    // Set up test configuration
    Config::set('services.kashier', [
        'merchant_id' => 'MID-12345',
        'api_key' => 'test-api-key-12345',
        'secret_key' => 'test-secret-key-12345',
        'mode' => 'test',
    ]);
});

describe('Configuration Methods', function () {
    it('returns merchant ID from config', function () {
        $merchantId = $this->kashierService->getMerchantId();

        expect($merchantId)->toBe('MID-12345');
    });

    it('returns API key from config', function () {
        $apiKey = $this->kashierService->getApiKey();

        expect($apiKey)->toBe('test-api-key-12345');
    });

    it('returns secret key from config', function () {
        $secretKey = $this->kashierService->getSecretKey();

        expect($secretKey)->toBe('test-secret-key-12345');
    });

    it('returns mode from config', function () {
        $mode = $this->kashierService->getMode();

        expect($mode)->toBe('test');
    });    it('returns default mode when not set in config', function () {
        // Create a new config without the mode key
        $kashierConfig = Config::get('services.kashier');
        unset($kashierConfig['mode']);
        Config::set('services.kashier', $kashierConfig);

        $mode = $this->kashierService->getMode();

        expect($mode)->toBe('test');
    });

    it('returns test API base URL when in test mode', function () {
        Config::set('services.kashier.mode', 'test');

        $baseUrl = $this->kashierService->getApiBaseUrl();

        expect($baseUrl)->toBe('https://test-api.kashier.io');
    });

    it('returns live API base URL when in live mode', function () {
        Config::set('services.kashier.mode', 'live');

        $baseUrl = $this->kashierService->getApiBaseUrl();

        expect($baseUrl)->toBe('https://api.kashier.io');
    });
});

describe('Payment Processing', function () {
    it('creates payment data for an order', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total' => 150.75,
        ]);

        $paymentData = $this->kashierService->pay($order);

        expect($paymentData)->toBeInstanceOf(KashierPaymentData::class);
        expect($paymentData->merchantId)->toBe('MID-12345');
        expect($paymentData->orderId)->toBe((string) $order->id);
        expect($paymentData->amount)->toBe('150.75');
        expect($paymentData->currency)->toBe('EGP');
        expect($paymentData->mode)->toBe('test');
        expect($paymentData->displayMode)->toBe('ar');
        expect($paymentData->allowedMethods)->toBe('card');
        expect($paymentData->additionalParams)->toBe(['orderId' => $order->id]);
        expect($paymentData->hash)->toBeString();
        expect(strlen($paymentData->hash))->toBe(64); // SHA256 hash length
    });

    it('generates correct hash for payment', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total' => 100.00,
        ]);

        $paymentData = $this->kashierService->pay($order);

        // Recreate the expected hash
        $path = "/?payment=MID-12345.{$order->id}.100.00.EGP";
        $expectedHash = hash_hmac('sha256', $path, 'test-api-key-12345');

        expect($paymentData->hash)->toBe($expectedHash);
    });

    it('formats amount correctly with decimal places', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total' => 99.9,
        ]);

        $paymentData = $this->kashierService->pay($order);

        expect($paymentData->amount)->toBe('99.90');
    });

    it('handles integer amounts correctly', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total' => 100,
        ]);

        $paymentData = $this->kashierService->pay($order);

        expect($paymentData->amount)->toBe('100.00');
    });
});

describe('Payment Response Validation', function () {
    it('validates correct payment response signature', function () {
        $params = [
            'paymentId' => 'PAY123',
            'orderId' => '1',
            'amount' => '100.00',
            'currency' => 'EGP',
            'status' => 'SUCCESS',
            'mode' => 'test',
        ];

        // Generate correct signature
        $queryString = 'paymentId=PAY123&orderId=1&amount=100.00&currency=EGP&status=SUCCESS';
        $correctSignature = hash_hmac('sha256', $queryString, 'test-api-key-12345');
        $params['signature'] = $correctSignature;

        $isValid = $this->kashierService->validatePaymentResponse($params);

        expect($isValid)->toBeTrue();
    });

    it('rejects incorrect payment response signature', function () {
        $params = [
            'paymentId' => 'PAY123',
            'orderId' => '1',
            'amount' => '100.00',
            'currency' => 'EGP',
            'status' => 'SUCCESS',
            'signature' => 'incorrect-signature',
            'mode' => 'test',
        ];

        $isValid = $this->kashierService->validatePaymentResponse($params);

        expect($isValid)->toBeFalse();
    });

    it('excludes signature and mode from signature calculation', function () {
        $params = [
            'paymentId' => 'PAY123',
            'orderId' => '1',
            'amount' => '100.00',
            'currency' => 'EGP',
            'status' => 'SUCCESS',
            'mode' => 'test',
            'extraParam' => 'should-be-included',
        ];

        // Generate signature without 'signature' and 'mode' parameters
        $queryString = 'paymentId=PAY123&orderId=1&amount=100.00&currency=EGP&status=SUCCESS&extraParam=should-be-included';
        $correctSignature = hash_hmac('sha256', $queryString, 'test-api-key-12345');
        $params['signature'] = $correctSignature;

        $isValid = $this->kashierService->validatePaymentResponse($params);

        expect($isValid)->toBeTrue();
    });
});

describe('URL Generation', function () {
    it('generates success redirect URL for order', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $url = $this->kashierService->getSuccessRedirectUrl($order);

        expect($url)->toContain('payments/kashier/success');
        expect($url)->toContain("order={$order->id}");
    });

    it('generates failure redirect URL for order', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $url = $this->kashierService->getFailureRedirectUrl($order);

        expect($url)->toContain('payments/kashier/failure');
        expect($url)->toContain("order={$order->id}");
    });

    it('generates webhook URL', function () {
        $url = $this->kashierService->getWebhookUrl();

        expect($url)->toContain('webhooks/kashier');
    });
});

describe('Successful Payment Processing', function () {
    it('processes successful payment and updates order', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $paymentData = [
            'paymentId' => 'PAY123456',
            'status' => 'SUCCESS',
            'amount' => '100.00',
            'currency' => 'EGP',
        ];

        $updatedOrder = $this->kashierService->processSuccessfulPayment($order, $paymentData);

        expect($updatedOrder->payment_status)->toBe(PaymentStatus::PAID);
        expect($updatedOrder->payment_id)->toBe('PAY123456');
        expect($updatedOrder->payment_details)->toBe(json_encode($paymentData));

        // Verify the order was saved to database
        $freshOrder = $order->fresh();
        expect($freshOrder->payment_status)->toBe(PaymentStatus::PAID);
        expect($freshOrder->payment_id)->toBe('PAY123456');
    });

    it('processes successful payment without payment ID', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $paymentData = [
            'status' => 'SUCCESS',
            'amount' => '100.00',
            'currency' => 'EGP',
        ];

        $updatedOrder = $this->kashierService->processSuccessfulPayment($order, $paymentData);

        expect($updatedOrder->payment_status)->toBe(PaymentStatus::PAID);
        expect($updatedOrder->payment_id)->toBeNull();
        expect($updatedOrder->payment_details)->toBe(json_encode($paymentData));
    });
});

describe('Webhook Registration', function () {
    it('registers webhook URL successfully', function () {
        Http::fake([
            'https://test-api.kashier.io/merchant*' => Http::response([
                'success' => true,
                'message' => 'Webhook registered successfully'
            ], 200)
        ]);

        $result = $this->kashierService->registerWebhookUrl();

        expect($result)->toBe([
            'success' => true,
            'message' => 'Webhook registered successfully'
        ]);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://test-api.kashier.io/merchant?action=webhook&operation=updatemerchantuser' &&
                   $request->method() === 'PUT' &&
                   $request->hasHeader('Authorization', 'test-secret-key-12345') &&
                   $request->data() === [
                       'MID' => 'MID-12345',
                       'webhookUrl' => route('kashier.payment.webhook')
                   ];
        });
    });

    it('throws exception when webhook registration fails', function () {
        Http::fake([
            'https://test-api.kashier.io/merchant*' => Http::response([
                'error' => 'Invalid merchant ID'
            ], 400)
        ]);

        expect(function () {
            $this->kashierService->registerWebhookUrl();
        })->toThrow(Exception::class, 'Failed to register webhook URL with Kashier');
    });

    it('uses live API URL when in live mode', function () {
        Config::set('services.kashier.mode', 'live');

        Http::fake([
            'https://api.kashier.io/merchant*' => Http::response([
                'success' => true
            ], 200)
        ]);

        $this->kashierService->registerWebhookUrl();

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'https://api.kashier.io/merchant');
        });
    });
});

describe('Webhook Status Check', function () {
    it('returns true when webhook is registered and enabled', function () {
        Http::fake([
            'https://test-api.kashier.io/merchant*' => Http::response([
                'body' => [
                    'webhook' => [
                        'isEnabled' => true,
                        'url' => 'https://example.com/webhook'
                    ]
                ]
            ], 200)
        ]);

        $isRegistered = $this->kashierService->isWebhookRegistered();

        expect($isRegistered)->toBeTrue();
    });

    it('returns false when webhook is not enabled', function () {
        Http::fake([
            'https://test-api.kashier.io/merchant*' => Http::response([
                'body' => [
                    'webhook' => [
                        'isEnabled' => false,
                        'url' => ''
                    ]
                ]
            ], 200)
        ]);

        $isRegistered = $this->kashierService->isWebhookRegistered();

        expect($isRegistered)->toBeFalse();
    });

    it('returns false when webhook URL is empty', function () {
        Http::fake([
            'https://test-api.kashier.io/merchant*' => Http::response([
                'body' => [
                    'webhook' => [
                        'isEnabled' => true,
                        'url' => ''
                    ]
                ]
            ], 200)
        ]);

        $isRegistered = $this->kashierService->isWebhookRegistered();

        expect($isRegistered)->toBeFalse();
    });

    it('returns null when unable to check webhook status', function () {
        Http::fake([
            'https://test-api.kashier.io/merchant*' => Http::response([], 500)
        ]);

        $isRegistered = $this->kashierService->isWebhookRegistered();

        expect($isRegistered)->toBeNull();
    });

    it('returns null when exception occurs', function () {
        Http::fake([
            'https://test-api.kashier.io/merchant*' => function () {
                throw new Exception('Network error');
            }
        ]);

        $isRegistered = $this->kashierService->isWebhookRegistered();

        expect($isRegistered)->toBeNull();
    });
});

describe('Refund Processing', function () {
    it('processes successful refund with full response', function () {
        $orderId = 'MID-12345.123.150.00.EGP';
        $amount = 150.00;
        $reason = 'Test refund';

        Http::fake([
            'https://test-fep.kashier.io/v3/orders/*' => Http::response([
                'status' => 'SUCCESS',
                'response' => [
                    'status' => 'REFUNDED',
                    'gatewayCode' => 'APPROVED',
                    'gatewayMessage' => 'Payment accepted',
                    'transactionResponseCode' => '00',
                    'transactionResponseMessage' => [
                        'en' => 'Approved',
                        'ar' => 'تمت الموافقة'
                    ],
                    'transactionId' => 'TX-69827599',
                    'transactionDate' => '2022-04-13T10:44:46.724Z',
                    'settlementDate' => '2022-04-13',
                    'amount' => 150.00,
                    'currency' => 'EGP',
                    'operation' => 'refund',
                    'merchantIdentifier' => 'TESTQNBINHOUSE03',
                    'cardOrderId' => '93c799ea-ae6c-485c-bf36-a385acbb8c13',
                    'creationDate' => '2022-04-13T10:44:46.724Z',
                    'orderReference' => 'TEST-ORD-37089',
                    'payload' => [
                        'order' => [
                            'status' => 'PARTIALLY_REFUNDED',
                            'totalRefundedAmount' => 150.00
                        ]
                    ]
                ],
                'messages' => [
                    'en' => 'Congratulations! Your refund was successful',
                    'ar' => 'تهانينا! تمت معاملة استرداد الأموال بنجاح'
                ]
            ], 200)
        ]);

        $refundRequest = new RefundRequestData($orderId, $amount, $reason);
        $result = $this->kashierService->processRefund($refundRequest);

        expect($result)->toBeInstanceOf(RefundResultData::class);
        expect($result->success)->toBeTrue();
        expect($result->transactionId)->toBe('TX-69827599');
        expect($result->cardOrderId)->toBe('93c799ea-ae6c-485c-bf36-a385acbb8c13');
        expect($result->orderReference)->toBe('TEST-ORD-37089');
        expect($result->gatewayCode)->toBe('APPROVED');
        expect($result->gatewayMessage)->toBe('Payment accepted');
        expect($result->transactionResponseCode)->toBe('00');
        expect($result->amount)->toBe(150.00);
        expect($result->currency)->toBe('EGP');
        expect($result->operation)->toBe('refund');
        expect($result->merchantIdentifier)->toBe('TESTQNBINHOUSE03');
        expect($result->messageEn)->toBe('Congratulations! Your refund was successful');
        expect($result->messageAr)->toBe('تهانينا! تمت معاملة استرداد الأموال بنجاح');
        expect($result->orderStatus)->toBe('PARTIALLY_REFUNDED');
        expect($result->totalRefundedAmount)->toBe(150.00);
        expect($result->fullResponse)->toBeArray();
    });

    it('uses correct endpoint URL for test mode', function () {
        $orderId = 'MID-12345.123.150.00.EGP';
        $amount = 150.00;

        Http::fake([
            'https://test-fep.kashier.io/v3/orders/*' => Http::response([
                'status' => 'SUCCESS',
                'response' => ['status' => 'REFUNDED'],
                'messages' => ['en' => 'Success']
            ], 200)
        ]);

        $refundRequest = new RefundRequestData($orderId, $amount);
        $this->kashierService->processRefund($refundRequest);

        Http::assertSent(function ($request) use ($orderId) {
            return $request->url() === "https://test-fep.kashier.io/v3/orders/{$orderId}/";
        });
    });

    it('uses correct endpoint URL for live mode', function () {
        Config::set('services.kashier.mode', 'live');

        $orderId = 'MID-12345.123.150.00.EGP';
        $amount = 150.00;

        Http::fake([
            'https://fep.kashier.io/v3/orders/*' => Http::response([
                'status' => 'SUCCESS',
                'response' => ['status' => 'REFUNDED'],
                'messages' => ['en' => 'Success']
            ], 200)
        ]);

        $refundRequest = new RefundRequestData($orderId, $amount);
        $this->kashierService->processRefund($refundRequest);

        Http::assertSent(function ($request) use ($orderId) {
            return $request->url() === "https://fep.kashier.io/v3/orders/{$orderId}/";
        });
    });

    it('sends correct request payload', function () {
        $orderId = 'MID-12345.123.150.00.EGP';
        $amount = 150.75;
        $reason = 'Customer requested refund';

        Http::fake([
            'https://test-fep.kashier.io/v3/orders/*' => Http::response([
                'status' => 'SUCCESS',
                'response' => ['status' => 'REFUNDED'],
                'messages' => ['en' => 'Success']
            ], 200)
        ]);

        $refundRequest = new RefundRequestData($orderId, $amount, $reason);
        $this->kashierService->processRefund($refundRequest);

        Http::assertSent(function ($request) use ($reason) {
            $payload = $request->data();
            return $payload['apiOperation'] === 'REFUND' &&
                   $payload['reason'] === $reason &&
                   $payload['transaction']['amount'] === 150.75;
        });
    });

    it('uses default reason when none provided', function () {
        $orderId = 'MID-12345.123.150.00.EGP';
        $amount = 150.00;

        Http::fake([
            'https://test-fep.kashier.io/v3/orders/*' => Http::response([
                'status' => 'SUCCESS',
                'response' => ['status' => 'REFUNDED'],
                'messages' => ['en' => 'Success']
            ], 200)
        ]);

        $refundRequest = new RefundRequestData($orderId, $amount);
        $this->kashierService->processRefund($refundRequest);

        Http::assertSent(function ($request) {
            $payload = $request->data();
            return $payload['reason'] === 'Order return refund';
        });
    });

    it('sends correct headers', function () {
        $orderId = 'MID-12345.123.150.00.EGP';
        $amount = 150.00;

        Http::fake([
            'https://test-fep.kashier.io/v3/orders/*' => Http::response([
                'status' => 'SUCCESS',
                'response' => ['status' => 'REFUNDED'],
                'messages' => ['en' => 'Success']
            ], 200)
        ]);

        $refundRequest = new RefundRequestData($orderId, $amount);
        $this->kashierService->processRefund($refundRequest);

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'test-secret-key-12345') &&
                   $request->hasHeader('accept', 'application/json') &&
                   $request->hasHeader('Content-Type', 'application/json');
        });
    });

    it('handles failed refund with error response', function () {
        $orderId = 'MID-12345.123.150.00.EGP';
        $amount = 150.00;

        Http::fake([
            'https://test-fep.kashier.io/v3/orders/*' => Http::response([
                'status' => 'FAILED',
                'response' => [
                    'status' => 'FAILED',
                    'gatewayCode' => 'DECLINED',
                    'gatewayMessage' => 'Insufficient funds',
                    'transactionResponseCode' => '51',
                    'transactionResponseMessage' => [
                        'en' => 'Insufficient funds',
                        'ar' => 'رصيد غير كافي'
                    ]
                ],
                'messages' => [
                    'en' => 'Refund failed',
                    'ar' => 'فشل الاسترداد'
                ]
            ], 400)
        ]);

        $refundRequest = new RefundRequestData($orderId, $amount);
        $result = $this->kashierService->processRefund($refundRequest);

        expect($result)->toBeInstanceOf(RefundResultData::class);
        expect($result->success)->toBeFalse();
        expect($result->messageEn)->toBe('Refund failed');
        expect($result->messageAr)->toBe('فشل الاسترداد');
        expect($result->gatewayCode)->toBe('DECLINED');
        expect($result->gatewayMessage)->toBe('Insufficient funds');
        expect($result->transactionResponseCode)->toBe('51');
        expect($result->transactionResponseMessageEn)->toBe('Insufficient funds');
        expect($result->transactionResponseMessageAr)->toBe('رصيد غير كافي');
        expect($result->fullResponse)->toBeArray();
    });

    it('handles network exception during refund', function () {
        $orderId = 'MID-12345.123.150.00.EGP';
        $amount = 150.00;

        Http::fake([
            'https://test-fep.kashier.io/v3/orders/*' => function () {
                throw new Exception('Network timeout');
            }
        ]);

        $refundRequest = new RefundRequestData($orderId, $amount);
        $result = $this->kashierService->processRefund($refundRequest);

        expect($result)->toBeInstanceOf(RefundResultData::class);
        expect($result->success)->toBeFalse();
        expect($result->messageEn)->toBe('Failed to process refund: Network timeout');
    });

    it('handles HTTP error status codes', function () {
        $orderId = 'MID-12345.123.150.00.EGP';
        $amount = 150.00;

        Http::fake([
            'https://test-fep.kashier.io/v3/orders/*' => Http::response([
                'status' => 'ERROR',
                'response' => [
                    'status' => 'ERROR',
                    'gatewayMessage' => 'Server error'
                ],
                'messages' => [
                    'en' => 'Internal server error'
                ]
            ], 500)
        ]);

        $refundRequest = new RefundRequestData($orderId, $amount);
        $result = $this->kashierService->processRefund($refundRequest);

        expect($result)->toBeInstanceOf(RefundResultData::class);
        expect($result->success)->toBeFalse();
        expect($result->messageEn)->toBe('Internal server error');
    });

    it('handles partial success scenarios', function () {
        $orderId = 'MID-12345.123.150.00.EGP';
        $amount = 150.00;

        Http::fake([
            'https://test-fep.kashier.io/v3/orders/*' => Http::response([
                'status' => 'SUCCESS',
                'response' => [
                    'status' => 'FAILED',
                    'gatewayCode' => 'DECLINED'
                ],
                'messages' => [
                    'en' => 'Gateway declined'
                ]
            ], 200)
        ]);

        $refundRequest = new RefundRequestData($orderId, $amount);
        $result = $this->kashierService->processRefund($refundRequest);

        expect($result)->toBeInstanceOf(RefundResultData::class);
        expect($result->success)->toBeFalse();
        expect($result->messageEn)->toBe('Gateway declined');
    });
});
