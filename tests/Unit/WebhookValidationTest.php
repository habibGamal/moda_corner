<?php

use App\Services\Payment\Validators\KashierPaymentValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Set up test configuration
    Config::set('services.kashier', [
        'api_key' => 'test-api-key-12345',
    ]);

    $this->validator = new KashierPaymentValidator;
});

describe('Webhook Data Validation', function () {
    it('validates webhook payment data structure correctly', function () {
        // This is the actual webhook data structure from the logs
        $webhookData = [
            'merchantOrderId' => 'Moda-9',
            'kashierOrderId' => '3f386d11-f3fe-4e0d-9839-eeb85ee5a65a',
            'orderReference' => 'TEST-ORD-193407389',
            'transactionId' => 'TX-29933712217',
            'status' => 'SUCCESS',
            'method' => 'card',
            'creationDate' => '2025-09-09T15:40:46.638Z',
            'amount' => 134.99,
            'currency' => 'EGP',
            'transactionResponseCode' => '00',
            'channel' => 'online | e-commerce',
        ];

        $result = $this->validator->validatePaymentResponse($webhookData);

        expect($result)->toBeTrue();
    });

    it('validates direct payment response data structure correctly', function () {
        // This is the direct payment response structure
        $paymentData = [
            'paymentId' => 'PAY123456',
            'orderId' => 'TEST-ORDER-123',
            'amount' => '150.00',
            'currency' => 'EGP',
            'signature' => hash_hmac('sha256', 'paymentId=PAY123456&orderId=TEST-ORDER-123&amount=150.00&currency=EGP', 'test-api-key-12345'),
        ];

        $result = $this->validator->validatePaymentResponse($paymentData);

        expect($result)->toBeTrue();
    });

    it('rejects invalid webhook data missing required fields', function () {
        $invalidWebhookData = [
            'merchantOrderId' => 'Moda-9',
            // Missing 'status' and 'transactionId'
            'amount' => 134.99,
            'currency' => 'EGP',
        ];

        $result = $this->validator->validatePaymentResponse($invalidWebhookData);

        expect($result)->toBeFalse();
    });

    it('rejects invalid direct payment response without signature', function () {
        $invalidPaymentData = [
            'paymentId' => 'PAY123456',
            'orderId' => 'TEST-ORDER-123',
            'amount' => '150.00',
            'currency' => 'EGP',
            // Missing 'signature'
        ];

        $result = $this->validator->validatePaymentResponse($invalidPaymentData);

        expect($result)->toBeFalse();
    });
});
