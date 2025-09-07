<?php

namespace App\Services;

use App\Models\Order;
use App\Interfaces\PaymentServiceInterface;
use App\DTOs\KashierPaymentData;
use App\DTOs\PaymentResultData;
use App\DTOs\RefundRequestData;
use App\DTOs\RefundResultData;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KashierPaymentService implements PaymentServiceInterface
{
    /**
     * Create a new service instance.
     */
    public function __construct()
    {
    }

    /**
     * Get the Kashier merchant ID from config
     */
    public function getMerchantId(): string
    {
        return config('services.kashier.merchant_id');
    }

    /**
     * Get the Kashier API key from config
     */
    public function getApiKey(): string
    {
        return config('services.kashier.api_key');
    }

    /**
     * Get the Kashier secret key from config (used for Authorization header)
     */
    public function getSecretKey(): string
    {
        return config('services.kashier.secret_key');
    }

    /**
     * Get the current mode (test/live) from config
     */
    public function getMode(): string
    {
        return config('services.kashier.mode', 'test');
    }

    /**
     * Get the Kashier API base URL based on mode
     */
    public function getApiBaseUrl(): string
    {
        return $this->getMode() === 'live'
            ? 'https://api.kashier.io'
            : 'https://test-api.kashier.io';
    }

    /**
     * Process payment for an order
     *
     * @param Order $order The order to process payment for
     * @return PaymentResultData The payment data for the Kashier payment form
     */
    public function pay(Order $order): PaymentResultData
    {
        // Generate a unique reference for this payment attempt
        $uniqueRef = $order->id;

        // Format amount with 2 decimal places
        $amount = number_format($order->total, 2, '.', '');

        // Generate the hash
        $merchantId = $this->getMerchantId();
        $currency = 'EGP'; // Default currency, can be made configurable
        $secret = $this->getApiKey();

        // Create the path string as per Kashier's documentation
        $path = "/?payment={$merchantId}.{$uniqueRef}.{$amount}.{$currency}";

        // Generate the hash using HMAC SHA256
        $hash = hash_hmac('sha256', $path, $secret);

        // Get the redirect URLs
        $redirectUrl = route('kashier.payment.success');
        $failureUrl = route('kashier.payment.failure');
        $webhookUrl = $this->getWebhookUrl();

        return new KashierPaymentData(
            merchantId: $merchantId,
            orderId: $uniqueRef,
            amount: $amount,
            currency: $currency,
            hash: $hash,
            mode: $this->getMode(),
            redirectUrl: $redirectUrl,
            failureUrl: $failureUrl,
            webhookUrl: $webhookUrl,
            displayMode: 'ar',
            paymentRequestId: uniqid('pr_'),
            allowedMethods: 'card',
            additionalParams: [
                'orderId' => $order->id,
            ]
        );
    }

    /**
     * Validate the payment response from Kashier
     *
     * @param array $params The response parameters from Kashier
     * @return bool Whether the signature is valid
     */
    public function validatePaymentResponse(array $params): bool
    {
        $secret = $this->getApiKey();
        $receivedSignature = $params['signature'];
        $queryString = "";
        foreach ($params as $key => $value) {
            if ($key === "signature" || $key === "mode") {
                continue;
            }
            $queryString .= "&{$key}={$value}";
        }

        $queryString = ltrim($queryString, '&');
        // Generate the expected signature
        $expectedSignature = hash_hmac('sha256', $queryString, $secret);

        // Compare the signatures (use timing-safe comparison)
        return hash_equals($expectedSignature, $receivedSignature);
    }


    /**
     * Get the redirect URL for successful payments
     *
     * @param Order $order
     * @return string
     */
    public function getSuccessRedirectUrl(Order $order): string
    {
        return route('kashier.payment.success', ['order' => $order->id]);
    }

    /**
     * Get the redirect URL for failed payments
     *
     * @param Order $order
     * @return string
     */
    public function getFailureRedirectUrl(Order $order): string
    {
        return route('kashier.payment.failure', ['order' => $order->id]);
    }

    /**
     * Get the webhook URL for server notifications from Kashier
     *
     * @return string
     */
    public function getWebhookUrl(): string
    {
        return route('kashier.payment.webhook');
    }

    /**
     * Process a successful payment for an order
     *
     * @param Order $order The order to update
     * @param array $paymentData The payment data from Kashier
     * @return Order The updated order
     */
    public function processSuccessfulPayment(Order $order, array $paymentData): Order
    {
        // Update the order payment status to paid
        $order->payment_status = \App\Enums\PaymentStatus::PAID;
        $order->payment_details = json_encode($paymentData);
        $order->payment_id = $paymentData['paymentId'] ?? null;
        $order->save();

        return $order;
    }

    /**
     * Register webhook URL with Kashier system
     * This method should be called once to register the webhook URL
     *
     * @return array Response from Kashier API
     * @throws \Exception If the registration fails
     */
    public function registerWebhookUrl(): array
    {
        $merchantId = $this->getMerchantId();
        $secretKey = $this->getSecretKey();
        $webhookUrl = $this->getWebhookUrl();
        $baseUrl = $this->getApiBaseUrl();

        $url = $baseUrl . '/merchant?action=webhook&operation=updatemerchantuser';

        $payload = [
            'MID' => $merchantId,
            'webhookUrl' => $webhookUrl
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => $secretKey,
                'accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->put($url, $payload);

            if ($response->successful()) {
                $responseData = $response->json();

                Log::info('Webhook URL registered successfully with Kashier', [
                    'merchant_id' => $merchantId,
                    'webhook_url' => $webhookUrl,
                    'response' => $responseData
                ]);

                return $responseData;
            } else {
                $errorMessage = 'Failed to register webhook URL with Kashier';
                $errorData = [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'merchant_id' => $merchantId,
                    'webhook_url' => $webhookUrl
                ];

                Log::error($errorMessage, $errorData);

                throw new \Exception($errorMessage . ': ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred while registering webhook URL', [
                'merchant_id' => $merchantId,
                'webhook_url' => $webhookUrl,
                'exception' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Check if webhook URL is registered with Kashier
     *
     * @return bool|null True if registered, false if not registered, null if unable to check
     */
    public function isWebhookRegistered(): ?bool
    {
        try {
            $merchantId = $this->getMerchantId();
            $secretKey = $this->getSecretKey();
            $baseUrl = $this->getApiBaseUrl();

            // This would be the endpoint to check webhook status
            // Note: This is an assumption - you might need to check Kashier's actual API documentation
            $url = $baseUrl . '/merchant?action=webhook&operation=getmerchantuser';

            $response = Http::withHeaders([
                'Authorization' => $secretKey,
                'accept' => 'application/json'
            ])->get($url, ['MID' => $merchantId]);

            if ($response->successful()) {
                $responseData = $response->json();

                // Check if webhook is enabled and has a URL
                if (isset($responseData['body']['webhook']['isEnabled']) &&
                    $responseData['body']['webhook']['isEnabled'] === true &&
                    !empty($responseData['body']['webhook']['url'])) {
                    return true;
                }

                return false;
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('Unable to check webhook registration status', [
                'exception' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Process refund for a payment using DTO
     *
     * @param RefundRequestData $refundRequest The refund request data
     * @return RefundResultData The refund result
     */
    public function processRefund(RefundRequestData $refundRequest): RefundResultData
    {
        $baseUrl = $this->getApiBaseUrl();
        $secretKey = $this->getSecretKey();

        // Use the correct endpoint structure based on Kashier documentation
        $url = $baseUrl === 'https://api.kashier.io'
            ? "https://fep.kashier.io/v3/orders/{$refundRequest->orderId}/"
            : "https://test-fep.kashier.io/v3/orders/{$refundRequest->orderId}/";

        $payload = [
            'apiOperation' => 'REFUND',
            'reason' => $refundRequest->reason ?? 'Order return refund',
            'transaction' => [
                'amount' => (float) number_format($refundRequest->amount, 2, '.', ''),
            ]
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => $secretKey,
                'accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->put($url, $payload);

            $responseData = $response->json();

            // Check for successful refund using the main status and response status
            if ($response->successful() &&
                isset($responseData['status']) && $responseData['status'] === 'SUCCESS' &&
                isset($responseData['response']['status']) && $responseData['response']['status'] === 'REFUNDED') {

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
}
