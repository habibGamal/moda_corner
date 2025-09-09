<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Interfaces\PaymentGatewayFactoryInterface;
use App\Models\Order;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected PaymentGatewayFactoryInterface $gatewayFactory;

    protected OrderService $orderService;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        PaymentGatewayFactoryInterface $gatewayFactory,
        OrderService $orderService
    ) {
        $this->gatewayFactory = $gatewayFactory;
        $this->orderService = $orderService;
    }

    /**
     * Handle webhook notifications (generic for all gateways)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleWebhook(Request $request, string $gateway)
    {
        try {
            Log::info('Payment webhook received', [
                'gateway' => $gateway,
                'params' => $request->all(),
            ]);

            // Get the appropriate gateway
            $paymentGateway = $this->gatewayFactory->createGateway($gateway);

            // Verify the webhook signature
            $validationResult = $paymentGateway->validatePaymentResponse($request->all());

            if (! $validationResult->isValid) {
                Log::warning('Invalid payment signature in webhook', [
                    'gateway' => $gateway,
                    'params' => $request->all(),
                    'error' => $validationResult->errorMessage,
                ]);

                return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 400);
            }

            // Extract order information
            $orderId = $validationResult->orderId;

            if (! $orderId) {
                Log::warning('No order ID found in payment webhook', [
                    'gateway' => $gateway,
                    'params' => $request->all(),
                ]);

                return response()->json(['status' => 'error', 'message' => 'No order ID found'], 400);
            }

            // Get the order
            $order = $this->orderService->getOrderById($orderId);

            if (! $order) {
                Log::warning('Order not found for payment webhook', [
                    'gateway' => $gateway,
                    'order_id' => $orderId,
                    'params' => $request->all(),
                ]);

                return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
            }

            // Process the payment if it's successful and not already processed
            if (in_array($validationResult->status, ['paid', 'SUCCESS']) && $order->payment_status !== PaymentStatus::PAID) {
                $paymentResponse = $paymentGateway->processSuccessfulPayment($order, $request->all());

                if ($paymentResponse->success) {
                    Log::info('Payment processed successfully via webhook', [
                        'gateway' => $gateway,
                        'order_id' => $order->id,
                        'transaction_id' => $validationResult->transactionId,
                    ]);
                } else {
                    Log::error('Failed to process payment via webhook', [
                        'gateway' => $gateway,
                        'order_id' => $order->id,
                        'error' => $paymentResponse->errorMessage,
                    ]);
                }
            }

            return response()->json(['status' => 'success']);

        } catch (Exception $e) {
            Log::error('Error processing payment webhook: '.$e->getMessage(), [
                'gateway' => $gateway,
                'exception' => $e,
                'params' => $request->all(),
            ]);

            return response()->json(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }
}
