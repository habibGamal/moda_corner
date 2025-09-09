<?php

namespace App\Services\Payment\Gateways;

use App\DTOs\Payment\CardPaymentData;
use App\DTOs\Payment\PaymentResponseData;
use App\DTOs\Payment\PaymentValidationData;
use App\DTOs\Payment\WalletPaymentData;
use App\DTOs\RefundRequestData;
use App\DTOs\RefundResultData;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Interfaces\PaymentGatewayInterface;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class KashierGateway implements PaymentGatewayInterface
{
    /**
     * Get the gateway identifier
     */
    public function getGatewayName(): string
    {
        return 'kashier';
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
     * Validate payment response from the payment gateway
     *
     * @param  array  $params  The response parameters from the payment gateway
     * @return PaymentValidationData The validation result
     */
    public function validatePaymentResponse(array $params): PaymentValidationData
    {
        try {
            $secret = $this->getApiKey();
            $receivedSignature = $params['signature'] ?? null;

            if (! $receivedSignature) {
                return PaymentValidationData::invalid('Missing signature in payment response', $params);
            }

            $queryString = '';
            foreach ($params as $key => $value) {
                if ($key === 'signature' || $key === 'mode') {
                    continue;
                }
                $queryString .= "&{$key}={$value}";
            }

            $queryString = ltrim($queryString, '&');
            // Generate the expected signature
            $expectedSignature = hash_hmac('sha256', $queryString, $secret);

            // Compare the signatures (use timing-safe comparison)
            if (! hash_equals($expectedSignature, $receivedSignature)) {
                return PaymentValidationData::invalid('Invalid signature', $params);
            }

            return PaymentValidationData::valid(
                transactionId: $params['paymentId'] ?? '',
                orderId: $params['merchantOrderId'] ?? '',
                amount: $params['amount'] ?? '',
                currency: $params['currency'] ?? 'EGP',
                status: $params['status'] ?? 'unknown',
                rawData: $params
            );
        } catch (\Exception $e) {
            Log::error('Kashier payment validation error', [
                'error' => $e->getMessage(),
                'params' => $params,
            ]);

            return PaymentValidationData::invalid(
                'Payment validation failed: '.$e->getMessage(),
                $params
            );
        }
    }

    /**
     * Process a successful payment for an order
     *
     * @param  Order  $order  The order to update
     * @param  array  $paymentData  The payment data from payment gateway
     * @return PaymentResponseData The payment processing result
     */
    public function processSuccessfulPayment(Order $order, array $paymentData): PaymentResponseData
    {
        try {
            // Update the order payment status to paid
            $order->payment_status = PaymentStatus::PAID;
            $order->payment_details = json_encode($paymentData);
            $order->payment_id = $paymentData['paymentId'] ?? null;
            $order->save();

            return PaymentResponseData::success(
                order: $order,
                transactionId: $paymentData['paymentId'] ?? '',
                paymentId: $paymentData['paymentId'] ?? null,
                paymentDetails: $paymentData,
                rawData: $paymentData
            );
        } catch (\Exception $e) {
            Log::error('Failed to process successful Kashier payment', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'payment_data' => $paymentData,
            ]);

            return PaymentResponseData::failure(
                order: $order,
                errorMessage: 'Failed to process payment: '.$e->getMessage(),
                rawData: $paymentData
            );
        }
    }

    /**
     * Get the webhook URL for the payment gateway
     */
    public function getWebhookUrl(): string
    {
        return route('payment.webhook', ['gateway' => 'kashier']);
    }

    /**
     * Check if the gateway supports the given payment method
     */
    public function supportsPaymentMethod(string $method): bool
    {
        return in_array($method, $this->getSupportedPaymentMethods());
    }

    /**
     * Get supported payment methods for this gateway
     */
    public function getSupportedPaymentMethods(): array
    {
        return [
            PaymentMethod::CARD->value,
            'card',
            'credit_card',
            'wallet',
        ];
    }

    /**
     * Process card payment without redirection
     *
     * @param  Order  $order  The order to process payment for
     * @param  CardPaymentData  $paymentData  The card payment data from the client
     * @return PaymentResponseData The payment processing result
     */
    public function payWithCard(Order $order, CardPaymentData $paymentData): PaymentResponseData
    {
        try {
            // Validate the payment data
            $validationErrors = $paymentData->validate();
            if (! empty($validationErrors)) {
                throw new \InvalidArgumentException('Validation failed: '.implode(', ', $validationErrors));
            }

            // Generate order hash for Kashier API
            $merchantId = $this->getMerchantId();
            $amount = number_format((float) $order->total, 2, '.', '');
            $currency = 'EGP';
            $orderReference = $order->id;
            $secret = $this->getApiKey();

            // Create the path string as per Kashier's documentation
            $path = "/?payment={$merchantId}.{$orderReference}.{$amount}.{$currency}";
            $hash = hash_hmac('sha256', $path, $secret);

            // Prepare the payment request payload
            $payload = [
                'apiOperation' => 'PAY',
                'paymentMethod' => [
                    'type' => 'CARD',
                    'card' => [
                        'save' => $paymentData->saveCard,
                        'expiry' => [
                            'month' => $paymentData->getFormattedExpiryMonth(),
                            'year' => $paymentData->expiryYear,
                        ],
                        'number' => $paymentData->cardNumber,
                        'nameOnCard' => $paymentData->nameOnCard,
                        'securityCode' => $paymentData->securityCode,
                    ],
                ],
                'order' => [
                    'reference' => (string) $orderReference,
                    'amount' => $amount,
                    'currency' => $currency,
                    'description' => "Payment for order #{$order->id}",
                ],
                'reconciliation' => [
                    'webhookUrl' => $this->getWebhookUrl(),
                    // 'merchantRedirect' => $this->getSuccessRedirectUrl($order),
                    'redirect' => false, // No redirect for direct payment
                ],
                'customer' => [
                    'reference' => (string) $order->user_id,
                    'firstName' => $order->user->name ?? 'Customer',
                    'lastName' => '',
                    'email' => $order->user->email ?? 'customer@example.com',
                ],
                'save' => true,
                'merchantId' => $merchantId,
            ];

            // Get the API URL based on mode
            $baseUrl = $this->getApiBaseUrl();
            $url = $baseUrl === 'https://api.kashier.io'
                ? 'https://fep.kashier.io/v3/orders/'
                : 'https://test-fep.kashier.io/v3/orders/';

            // Make the API call to Kashier
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Kashier-Hash' => $hash,
            ])
                ->withOptions([
                    'verify' => app()->environment('local') ? false : true,
                ])
                ->post($url, $payload);

            $responseData = $response->json();

            // Log the response for debugging
            Log::info('Kashier direct payment response', [
                'order_id' => $order->id,
                'status' => $response->status(),
                'response' => $responseData,
            ]);

            // Check the payment result
            $result = $responseData['response']['result'] ?? null;
            $status = $responseData['response']['status'] ?? null;

            if ($result === 'SUCCESS' && in_array($status, ['CAPTURED', 'AUTHORIZED'])) {
                // Payment was successful
                $transactionId = $responseData['transactionId'] ?? $responseData['response']['transactionId'] ?? '';

                // Update order status
                $order->payment_status = PaymentStatus::PAID;
                $order->payment_details = json_encode($responseData);
                $order->payment_id = $transactionId;
                $order->save();

                return PaymentResponseData::success(
                    order: $order,
                    transactionId: $transactionId,
                    paymentId: $transactionId,
                    paymentDetails: $responseData['response'] ?? $responseData,
                    rawData: $responseData
                );
            } elseif ($result === 'SUCCESS' && $status === 'AUTHENTICATION_INITIATED') {
                // 3DS authentication required
                $authRedirectUrl = $responseData['response']['authentication']['redirectUrl'] ?? null;

                if ($authRedirectUrl) {
                    return PaymentResponseData::failure(
                        order: $order,
                        errorMessage: '3D Secure authentication required',
                        rawData: array_merge($responseData, [
                            'requires_3ds' => true,
                            'redirect_url' => $authRedirectUrl,
                            'redirect_html' => $responseData['response']['authentication']['redirectHtml'] ?? null,
                        ])
                    );
                }
            }

            // Payment failed
            $errorMessage = $responseData['response']['transactionResponseMessage']['en'] ??
                $responseData['message'] ??
                'Payment failed';

            throw ValidationException::withMessages(['payment' => $errorMessage]);


            // return PaymentResponseData::failure(
            //     order: $order,
            //     errorMessage: $errorMessage,
            //     rawData: $responseData
            // );

        } catch (\Exception $e) {
            Log::error('Kashier direct payment failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'payment_data' => $paymentData->toArray(),
            ]);

            throw $e;
        }
    }

    /**
     * Process wallet payment
     *
     * @param  Order  $order  The order to process payment for
     * @param  WalletPaymentData  $paymentData  The wallet payment data from the client
     * @return PaymentResponseData The payment processing result
     */
    public function payWithWallet(Order $order, WalletPaymentData $paymentData): PaymentResponseData
    {
        try {
            // Validate the payment data
            $validationErrors = $paymentData->validate();
            if (! empty($validationErrors)) {
                throw new \InvalidArgumentException('Validation failed: '.implode(', ', $validationErrors));
            }

            // Generate order reference with timestamp
            $timestamp = now()->timestamp;
            $orderReference = "PM-{$timestamp}";
            $amount = number_format((float) $order->total, 2, '.', '');
            $currency = 'EGP';

            // Prepare the wallet payment request payload
            $payload = [
                'apiOperation' => 'INITIATE_R2P',
                'paymentMethod' => [
                    'type' => 'wallet',
                ],
                'order' => [
                    'reference' => $orderReference,
                    'amount' => (float) $amount,
                    'currency' => $currency,
                ],
                'customer' => [
                    'mobilePhone' => $paymentData->mobilePhone,
                ],
                'interactionSource' => 'ECOMMERCE',
                'reconciliation' => [
                    'webhookUrl' => $this->getWebhookUrl(),
                ],
                'merchantId' => $this->getMerchantId(),
            ];

            // Get the API URL based on mode
            $baseUrl = $this->getApiBaseUrl();
            $url = $baseUrl === 'https://api.kashier.io'
                ? 'https://fep.kashier.io/v3/orders/'
                : 'https://test-fep.kashier.io/v3/orders/';

            // Generate hash for Kashier API
            $path = "/?payment={$this->getMerchantId()}.{$orderReference}.{$amount}.{$currency}";
            $hash = hash_hmac('sha256', $path, $this->getApiKey());

            // Make the API call to Kashier
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Kashier-Hash' => $hash,
            ])
                ->withOptions([
                    'verify' => app()->environment('local') ? false : true,
                ])
                ->post($url, $payload);

            $responseData = $response->json();

            // Log the response for debugging
            Log::info('Kashier wallet payment response', [
                'order_id' => $order->id,
                'status' => $response->status(),
                'response' => $responseData,
            ]);

            // Check if the response was successful
            if (! $response->successful()) {
                return PaymentResponseData::failure(
                    order: $order,
                    errorMessage: 'Wallet payment API call failed: '.($responseData['message'] ?? 'Unknown error'),
                    rawData: $responseData
                );
            }

            // Check the payment result
            $result = $responseData['response']['result'] ?? null;
            $status = $responseData['response']['status'] ?? null;

            if ($result === 'SUCCESS' && $status === 'SUCCESS') {
                // Wallet payment initiation was successful
                $transactionId = $responseData['response']['transactionId'] ?? '';
                $systemOrderId = $responseData['response']['order']['systemOrderId'] ?? '';

                // Update order with pending wallet payment status
                $order->payment_status = PaymentStatus::PENDING;
                $order->payment_details = json_encode($responseData);
                $order->payment_id = $transactionId;
                $order->save();

                return PaymentResponseData::success(
                    order: $order,
                    transactionId: $transactionId,
                    paymentId: $transactionId,
                    paymentDetails: array_merge($responseData['response'] ?? $responseData, [
                        'payment_type' => 'wallet',
                        'system_order_id' => $systemOrderId,
                        'order_reference' => $orderReference,
                        'mobile_phone' => $paymentData->mobilePhone,
                    ]),
                    rawData: $responseData
                );
            }

            // Payment initiation failed
            $errorMessage = $responseData['response']['transactionResponseMessage']['en'] ??
                $responseData['message'] ??
                'Wallet payment initiation failed';

            return PaymentResponseData::failure(
                order: $order,
                errorMessage: $errorMessage,
                rawData: $responseData
            );

        } catch (\Exception $e) {
            Log::error('Kashier wallet payment failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'payment_data' => $paymentData->toArray(),
            ]);

            throw $e;
        }
    }

    /**
     * Process refund for a payment using DTO (backward compatibility)
     *
     * @param  RefundRequestData  $refundRequest  The refund request data
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
            ],
        ];

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
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
}
