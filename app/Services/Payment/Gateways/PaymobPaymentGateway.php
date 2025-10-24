<?php

namespace App\Services\Payment\Gateways;

use App\DTOs\PaymentResultData;
use App\DTOs\PaymobPaymentData;
use App\DTOs\RefundRequestData;
use App\DTOs\RefundResultData;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Paymob payment gateway implementation
 * Follows Single Responsibility Principle - only handles Paymob payment processing
 * Extends AbstractPaymentGateway following Template Method pattern
 */
class PaymobPaymentGateway extends AbstractPaymentGateway
{
    protected string $secretKey;

    protected string $integrationId;

    protected string $iframeId;

    protected string $mode;

    public function __construct(
        \App\Interfaces\PaymentValidatorInterface $validator,
        \App\Interfaces\PaymentUrlProviderInterface $urlProvider,
        ?string $secretKey = null,
        ?string $integrationId = null,
        ?string $iframeId = null,
        ?string $mode = null
    ) {
        parent::__construct($validator, $urlProvider);
        $this->secretKey = $secretKey ?? config('services.paymob.secret_key');
        $this->integrationId = $integrationId ?? config('services.paymob.integration_id');
        $this->iframeId = $iframeId ?? config('services.paymob.iframe_id');
        $this->mode = $mode ?? config('services.paymob.mode', 'test');
    }

    /**
     * Get the gateway identifier
     */
    public function getGatewayId(): string
    {
        return 'paymob';
    }

    /**
     * Check feature support for Paymob
     */
    public function supports(string $feature): bool
    {
        return match ($feature) {
            'refunds' => true,
            'webhooks' => true,
            'recurring' => false,
            'cards' => true,
            'wallets' => true,
            default => parent::supports($feature),
        };
    }

    /**
     * Create Paymob-specific payment data using Unified Intention API
     */
    protected function createPaymentData(Order $order): PaymentResultData
    {
        if($order->payment_details){
            $paymentDetails = json_decode($order->payment_details, true);
            return new PaymobPaymentData(
                merchantId: config('services.paymob.public_key'),
                orderId: $paymentDetails['merchant_order_id'] ?? '',
                amount: ($paymentDetails['amount_cents'] ?? 0) / 100,
                currency: $paymentDetails['currency'] ?? 'EGP',
                hash: '', // Not used in Paymob Unified Checkout
                mode: $this->mode,
                redirectUrl: $this->urlProvider->getSuccessRedirectUrl($order),
                failureUrl: $this->urlProvider->getFailureRedirectUrl($order),
                webhookUrl: $this->urlProvider->getWebhookUrl(),
                iframeUrl: $paymentDetails['iframe_url'] ?? '',
                intentionId: $paymentDetails['intention_id'] ?? '',
                clientSecret: $paymentDetails['client_secret'] ?? '',
                additionalParams: [
                    'orderId' => $order->id,
                    'amountCents' => $paymentDetails['amount_cents'] ?? 0,
                ]
            );
        }
        $baseUrl = $this->getApiBaseUrl();
        $merchantOrderId = generateMerchantOrderNumber($order->id);
        $amountCents = (int) ($order->total * 100); // Amount in cents (smallest currency unit)

        // Prepare billing data
        $billingData = [
            'apartment' => $order->address->apartment_no ?? 'NA',
            'floor' => $order->address->floor_no ?? 'NA',
            'street' => $order->address->street ?? 'NA',
            'building' => $order->address->building_no ?? 'NA',
            'phone_number' => $order->user->phone ?? 'NA',
            'city' => $order->address->city->name_en ?? 'NA',
            'country' => 'EG',
            'email' => $order->user->email ?? 'NA',
            'first_name' => $order->user->name ?? 'Customer',
            'last_name' => '.',
        ];

        // Prepare items - Paymob requires 'amount_cents' as integer for each item
        $items = [];
        // foreach ($order->items as $item) {
        //     $items[] = [
        //         'name' => $item->product->name_en ?? $item->product->name_ar,
        //         'amount' => (int) ($item->price * 100),
        //         'description' => $item->product->description_en ?? $item->product->description_ar ?? '',
        //         'quantity' => (int) $item->quantity,
        //     ];
        // }

        // Prepare intention request
        $payload = [
            'amount' => $amountCents,
            'currency' => 'EGP',
            'payment_methods' => [(int) $this->integrationId],
            'items' => $items,
            'billing_data' => $billingData,
            'customer' => [
                'first_name' => $order->user->name ?? 'Customer',
                'last_name' => '.',
                'email' => $order->user->email ?? 'customer@example.com',
                'phone_number' => $order->user->phone ?? '+201000000000',
            ],
            'extras' => [
                'ee' => $merchantOrderId, // Extra field for merchant order ID
            ],
            'merchant_order_id' => $merchantOrderId,
            'special_reference' => $merchantOrderId,
            'redirection_url' => $this->urlProvider->getSuccessRedirectUrl($order),
            'notification_url' => $this->urlProvider->getWebhookUrl(),
        ];

        try {
            Log::info('Creating Paymob payment intention', [
                'order_id' => $order->id,
                'merchant_order_id' => $merchantOrderId,
                'amount_cents' => $amountCents,
                'payload' => $payload,
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])
                ->post($baseUrl . '/v1/intention/', $payload);

            if (!$response->successful()) {
                Log::error('Paymob intention creation failed', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);

                throw new \RuntimeException('Failed to create payment intention: ' . $response->body());
            }

            $responseData = $response->json();
            $clientSecret = $responseData['client_secret'] ?? '';
            $intentionId = $responseData['id'] ?? '';

            // Build iframe URL
            $iframeUrl = "https://accept.paymob.com/unifiedcheckout/?publicKey=" .
                urlencode(config('services.paymob.public_key')) .
                "&clientSecret=" . urlencode($clientSecret);

            Log::info('Paymob payment intention created successfully', [
                'order_id' => $order->id,
                'intention_id' => $intentionId,
                'iframe_url' => $iframeUrl,
            ]);

            $paymentData = new PaymobPaymentData(
                merchantId: config('services.paymob.public_key'),
                orderId: $merchantOrderId,
                amount: $amountCents / 100, // Convert back to display amount
                currency: 'EGP',
                hash: '', // Not used in Paymob Unified Checkout
                mode: $this->mode,
                redirectUrl: $this->urlProvider->getSuccessRedirectUrl($order),
                failureUrl: $this->urlProvider->getFailureRedirectUrl($order),
                webhookUrl: $this->urlProvider->getWebhookUrl(),
                iframeUrl: $iframeUrl,
                intentionId: $intentionId,
                clientSecret: $clientSecret,
                additionalParams: [
                    'orderId' => $order->id,
                    'amountCents' => $amountCents,
                ]
            );

            // Save payment details to order for later retrieval
            $order->update([
                'payment_details' => json_encode([
                    'intention_id' => $intentionId,
                    'client_secret' => $clientSecret,
                    'iframe_url' => $iframeUrl,
                    'merchant_order_id' => $merchantOrderId,
                    'amount_cents' => $amountCents,
                    'currency' => 'EGP',
                    'created_at' => now()->toISOString(),
                ]),
            ]);

            return $paymentData;

        } catch (\Exception $e) {
            Log::error('Exception creating Paymob payment intention', [
                'order_id' => $order->id,
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Execute refund through Paymob API
     */
    protected function executeRefund(RefundRequestData $refundRequest): RefundResultData
    {
        $baseUrl = $this->getApiBaseUrl();
        $amountCents = (int) ($refundRequest->amount * 100); // Amount in cents

        // First, we need to get the transaction ID from the order
        $order = Order::find($refundRequest->orderId);
        $paymobTransactionId = json_decode($order->payment_details)->id ?? null;
        if (!$order || !$paymobTransactionId) {
            Log::error('Cannot refund: Order or transaction ID not found', [
                'order_id' => $refundRequest->orderId,
            ]);

            return RefundResultData::failure([
                'error' => 'Order or transaction ID not found',
            ]);
        }

        $payload = [
            'transaction_id' => $paymobTransactionId,
            'amount_cents' => $amountCents,
        ];

        try {
            Log::info('Initiating Paymob refund', [
                'order_id' => $refundRequest->orderId,
                'paymob_order_id' => $paymobTransactionId,
                'amount_cents' => $amountCents,
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])
                ->post($baseUrl . '/api/acceptance/void_refund/refund', $payload);

            $responseData = $response->json();
            if ($response->successful() && isset($responseData['id'])) {
                Log::info('Paymob refund successful', [
                    'order_id' => $refundRequest->orderId,
                    'refund_id' => $responseData['id'],
                    'amount_cents' => $amountCents,
                ]);

                return RefundResultData::success($responseData);
            } else {
                Log::error('Paymob refund failed', [
                    'order_id' => $refundRequest->orderId,
                    'status' => $response->status(),
                    'response' => $responseData,
                ]);

                return RefundResultData::failure($responseData);
            }
        } catch (\Exception $e) {
            Log::error('Paymob refund API call failed', [
                'order_id' => $refundRequest->orderId,
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
            ? 'https://accept.paymob.com'
            : 'https://accept.paymob.com'; // Paymob uses same URL for test/live
    }
}
