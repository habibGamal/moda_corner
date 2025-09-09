<?php

namespace App\Services\Payment\Gateways;

use App\DTOs\KashierPaymentData;
use App\DTOs\PaymentResultData;
use App\DTOs\RefundRequestData;
use App\DTOs\RefundResultData;
use App\Enums\PaymentMethod;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Kashier payment gateway implementation
 * Follows Single Responsibility Principle - only handles Kashier payment processing
 * Extends AbstractPaymentGateway following Template Method pattern
 */
class KashierPaymentGateway extends AbstractPaymentGateway
{
    protected string $merchantId;

    protected string $apiKey;

    protected string $mode;

    public function __construct(
        \App\Interfaces\PaymentValidatorInterface $validator,
        \App\Interfaces\PaymentUrlProviderInterface $urlProvider,
        ?string $merchantId = null,
        ?string $apiKey = null,
        ?string $mode = null
    ) {
        parent::__construct($validator, $urlProvider);
        $this->merchantId = $merchantId ?? config('services.kashier.merchant_id');
        $this->apiKey = $apiKey ?? config('services.kashier.api_key');
        $this->mode = $mode ?? config('services.kashier.mode', 'test');
    }

    /**
     * Get the gateway identifier
     */
    public function getGatewayId(): string
    {
        return 'kashier';
    }

    /**
     * Check feature support for Kashier
     */
    public function supports(string $feature): bool
    {
        return match ($feature) {
            'refunds' => true,
            'webhooks' => true,
            'recurring' => false,
            'cards' => true,
            'wallets' => false,
            default => parent::supports($feature),
        };
    }

    /**
     * Create Kashier-specific payment data
     */
    protected function createPaymentData(Order $order): PaymentResultData
    {
        $uniqueRef = config('app.name').'-'.$order->id;
        $amount = number_format((float) $order->total, 2, '.', '');
        $currency = 'EGP';

        $path = "/?payment={$this->merchantId}.{$uniqueRef}.{$amount}.{$currency}";
        $hash = hash_hmac('sha256', $path, $this->apiKey);

        $redirectUrl = $this->urlProvider->getSuccessRedirectUrl($order);
        $failureUrl = $this->urlProvider->getFailureRedirectUrl($order);
        $webhookUrl = $this->urlProvider->getWebhookUrl();

        Log::info('Kashier Payment Data Generated', [
            'merchant_id' => $this->merchantId,
            'order_id' => $uniqueRef,
            'amount' => $amount,
            'currency' => $currency,
            'hash' => $hash,
            'mode' => $this->mode,
            'redirect_url' => $redirectUrl,
            'failure_url' => $failureUrl,
            'webhook_url' => $webhookUrl,
        ]);

        $allowdMethods = match ($order->payment_method) {
            PaymentMethod::CREDIT_CARD => 'card',
            PaymentMethod::WALLET => 'wallet',
            default => 'card',
        };

        return new KashierPaymentData(
            merchantId: $this->merchantId,
            orderId: $uniqueRef,
            amount: $amount,
            currency: $currency,
            hash: $hash,
            mode: $this->mode,
            redirectUrl: $redirectUrl,
            failureUrl: $failureUrl,
            webhookUrl: $webhookUrl,
            displayMode: 'ar',
            paymentRequestId: uniqid('pr_'),
            allowedMethods: $allowdMethods,
            additionalParams: [
                'orderId' => $order->id,
            ]
        );
    }

    /**
     * Execute refund through Kashier API
     */
    protected function executeRefund(RefundRequestData $refundRequest): RefundResultData
    {
        $baseUrl = $this->getApiBaseUrl();
        $secretKey = config('services.kashier.secret_key');

        // Use the correct endpoint structure based on Kashier documentation
        $url = $baseUrl === 'https://api.kashier.io'
            ? "https://fep.kashier.io/v3/orders/{$refundRequest->orderId}/"
            : "https://test-fep.kashier.io/v3/orders/{$refundRequest->orderId}/";

        $payload = [
            'apiOperation' => 'REFUND',
            'reason' => $refundRequest->reason ?? 'Order return refund',
            'transaction' => [
                'amount' => (float) number_format($refundRequest->amount, 2, '.', ''),
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => $secretKey,
                'accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->put($url, $payload);

            $responseData = $response->json();

            // Check for successful refund using the main status and response status
            if (
                $response->successful() &&
                isset($responseData['status']) && $responseData['status'] === 'SUCCESS' &&
                isset($responseData['response']['status']) && $responseData['response']['status'] === 'REFUNDED'
            ) {

                Log::info('Kashier refund successful', [
                    'order_id' => $refundRequest->orderId,
                    'amount' => $refundRequest->amount,
                    'transaction_id' => $responseData['response']['transactionId'] ?? null,
                    'gateway_code' => $responseData['response']['gatewayCode'] ?? null,
                    'card_order_id' => $responseData['response']['cardOrderId'] ?? null,
                    'order_reference' => $responseData['response']['orderReference'] ?? null,
                ]);

                return RefundResultData::success($responseData);
            } else {
                Log::error('Kashier refund failed', [
                    'order_id' => $refundRequest->orderId,
                    'amount' => $refundRequest->amount,
                    'status' => $responseData['status'] ?? null,
                    'response_status' => $responseData['response']['status'] ?? null,
                    'gateway_code' => $responseData['response']['gatewayCode'] ?? null,
                    'transaction_response_code' => $responseData['response']['transactionResponseCode'] ?? null,
                    'full_response' => $responseData,
                ]);

                return RefundResultData::failure($responseData);
            }
        } catch (\Exception $e) {
            Log::error('Kashier refund API call failed', [
                'order_id' => $refundRequest->orderId,
                'amount' => $refundRequest->amount,
                'exception' => $e->getMessage(),
            ]);

            return RefundResultData::exception($e->getMessage());
        }
    }

    /**
     * Get the API base URL based on mode
     */
    public function getApiBaseUrl(): string
    {
        return $this->mode === 'live'
            ? 'https://api.kashier.io'
            : 'https://test-api.kashier.io';
    }
}
