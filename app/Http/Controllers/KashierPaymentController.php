<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Http\Requests\KashierPaymentRequest;
use App\Models\Order;
use App\Interfaces\PaymentServiceInterface;
use App\Services\OrderService;
use App\Services\OrderEvaluationService;
use App\DTOs\OrderPlacementData;
use App\DTOs\KashierPaymentData;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class KashierPaymentController extends Controller
{
    protected PaymentServiceInterface $paymentService;
    protected OrderService $orderService;
    protected OrderEvaluationService $orderEvaluationService;

    /**
     * Create a new controller instance.
     *
     * @param PaymentServiceInterface $paymentService
     * @param OrderService $orderService
     * @param OrderEvaluationService $orderEvaluationService
     */
    public function __construct(
        PaymentServiceInterface $paymentService,
        OrderService $orderService,
        OrderEvaluationService $orderEvaluationService
    ) {
        $this->paymentService = $paymentService;
        $this->orderService = $orderService;
        $this->orderEvaluationService = $orderEvaluationService;
    }

    /**
     * Show the payment page for an existing order
     *
     * @param Request $request
     * @return \Inertia\Response|\Illuminate\Http\RedirectResponse
     */
    public function initiatePayment(Request $request)
    {
        try {
            $orderId = $request->input('order_id');
            // Get the order
            $order = $this->orderService->getOrderById($orderId);

            // Check if order is already paid
            if ($order->payment_status->isPaid()) {
                return redirect()->route('orders.show', $order->id)
                    ->with('info', 'This order has already been paid.');
            }

            // Check if order is using Kashier payment method
            if ($order->payment_method !== PaymentMethod::KASHIER) {
                return redirect()->route('orders.show', $order->id)
                    ->with('error', 'This order does not use online payment.');
            }

            // Generate payment data using our payment service
            $paymentData = $this->paymentService->pay($order);

            // Store order ID in session for the callback
            session()->put('kashier_order_id', $order->id);

            return Inertia::render('Payments/Kashier', [
                'kashierParams' => $paymentData instanceof \App\DTOs\KashierPaymentData ? $paymentData->toArray() : [],
                'order' => $order
            ]);
        } catch (Exception $e) {
            Log::error('Error initializing Kashier payment: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('orders.index')
                ->with('error', 'Unable to process payment. Please try again later or contact support.');
        }
    }

    /**
     * Handle the payment success callback from Kashier
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleSuccess(Request $request)
    {
        try {
            // Retrieve order ID from session
            $orderId = session()->get('kashier_order_id');

            if (!$orderId) {
                Log::error('Order ID not found in session', [
                    'params' => $request->all()
                ]);
                return redirect()->route('checkout.index')
                    ->with('error', 'Payment information not found. Please try again.');
            }

            // Get the order
            $order = $this->orderService->getOrderById($orderId);

            // Verify the payment response
            if (!$this->paymentService->validatePaymentResponse($request->all())) {
                Log::warning('Invalid Kashier signature in success callback', [
                    'params' => $request->all()
                ]);

                return redirect()->route('checkout.index')
                    ->with('error', 'Payment verification failed. Please contact support.');
            }

            // Payment data from Kashier
            $paymentData = $request->all();

            // Process the successful payment
            $this->paymentService->processSuccessfulPayment($order, $paymentData);

            // Clear session data
            session()->forget('kashier_order_id');

            return redirect()->route('orders.show', $order->id)
                ->with('success', 'Payment completed successfully! Your order is being processed.');

        } catch (Exception $e) {
            Log::error('Error handling payment success: ' . $e->getMessage(), [
                'exception' => $e,
                'params' => $request->all()
            ]);

            return redirect()->route('checkout.index')
                ->with('error', 'Error processing your payment. Please try again or contact support.');
        }
    }

    /**
     * Handle the payment failure callback from Kashier
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleFailure(Request $request)
    {
        try {
            // Get order ID from session
            $orderId = session()->get('kashier_order_id');

            // Clear session data
            session()->forget('kashier_order_id');

            // Log the failure details
            Log::info('Payment failed', [
                'params' => $request->all(),
                'orderId' => $orderId
            ]);

            // If we have an order ID, we could mark it as payment failed
            if ($orderId) {
                try {
                    $order = $this->orderService->getOrderById($orderId);
                    $order->payment_status = PaymentStatus::FAILED;
                    $order->save();
                } catch (Exception $orderEx) {
                    Log::warning('Failed to update order payment status: ' . $orderEx->getMessage());
                }
            }

            return redirect()->route('checkout.index')
                ->with('error', 'Payment was not successful. Please try again or use a different payment method.');

        } catch (Exception $e) {
            Log::error('Error handling payment failure: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return redirect()->route('checkout.index')
                ->with('error', 'Error processing your payment. Please try again or contact support.');
        }
    }

    public function handleWebhook(Request $request)
    {
        try {
            // Get the raw payload and headers for proper signature validation
            $rawPayload = $request->getContent();
            $headers = $request->headers->all();

            Log::info('Kashier webhook received', [
                'payload' => $request->all(),
                'raw_payload_length' => strlen($rawPayload),
                'headers' => $headers
            ]);

            // Verify the signature using the new webhook validation method
            if (!$this->paymentService->validatePaymentResponseWebhook($rawPayload, $headers)) {
                Log::warning('Invalid Kashier signature in webhook', [
                    'params' => $request->all(),
                    'raw_payload' => $rawPayload,
                    'headers' => $headers
                ]);
                return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 400);
            }

            // Get payment data from the webhook
            $webhookData = $request->all();
            $merchantOrderId = $webhookData['data']['merchantOrderId'] ?? null;
            $status = $webhookData['data']['status'] ?? null;
            $transactionId = $webhookData['data']['transactionId'] ?? null;
            $amount = $webhookData['data']['amount'] ?? null;

            if (!$merchantOrderId) {
                Log::error('Missing merchantOrderId in webhook data', ['webhook_data' => $webhookData]);
                return response()->json(['status' => 'error', 'message' => 'Missing order reference'], 400);
            }

            // Extract order ID from merchantOrderId (e.g., "Moda-5" -> 5)
            $orderId = str_replace(config('app.name') . '-', '', $merchantOrderId);

            if (!$orderId) {
                Log::error('Could not extract order ID from merchantOrderId', [
                    'merchantOrderId' => $merchantOrderId,
                    'webhook_data' => $webhookData
                ]);
                return response()->json(['status' => 'error', 'message' => 'Invalid order reference'], 400);
            }

            // Get the order
            $order = Order::find($orderId);

            if (!$order) {
                Log::error('Order not found', ['order_id' => $orderId, 'merchantOrderId' => $merchantOrderId]);
                return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
            }

            // Check if the payment was successful
            if ($status === 'SUCCESS') {
                // Process the successful payment
                $this->paymentService->processSuccessfulPayment($order, $webhookData['data']);

                Log::info('Kashier payment confirmed via webhook', [
                    'order_id' => $orderId,
                    'merchantOrderId' => $merchantOrderId,
                    'transactionId' => $transactionId,
                    'amount' => $amount,
                    'status' => $status
                ]);

                return response()->json(['status' => 'success'], 200);
            } else {
                // Handle failed payment
                $order->payment_status = PaymentStatus::FAILED;
                $order->payment_details = json_encode($webhookData['data']);
                $order->save();

                Log::warning('Kashier payment failed via webhook', [
                    'order_id' => $orderId,
                    'merchantOrderId' => $merchantOrderId,
                    'status' => $status,
                    'webhook_data' => $webhookData['data']
                ]);

                return response()->json(['status' => 'success'], 200); // Still return 200 to acknowledge receipt
            }

        } catch (Exception $e) {
            Log::error('Error processing Kashier webhook: ' . $e->getMessage(), [
                'exception' => $e,
                'payload' => $request->all()
            ]);
            return response()->json(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    /**
     * Show the payment iframe page for an existing order
     *
     * @param int $orderId
     * @return \Inertia\Response|\Illuminate\Http\RedirectResponse
     */
    public function showPayment(int $orderId)
    {
        try {
            $order = $this->orderService->getOrderById($orderId);

            // Check if order is already paid
            if ($order->payment_status === PaymentStatus::PAID) {
                return redirect()->route('orders.show', $order->id)
                    ->with('info', 'This order has already been paid.');
            }

            if ($order->payment_method !== PaymentMethod::KASHIER) {
                return redirect()->route('orders.show', $order->id)
                    ->with('error', 'This order does not use online payment.');
            }

            // Generate payment data for this order
            $paymentData = $this->paymentService->pay($order);

            // Store order ID in session for success/failure handler
            session()->put('kashier_order_id', $order->id);

            return Inertia::render('Payments/Kashier', [
                'order' => $order,
                'kashierParams' => $paymentData instanceof \App\DTOs\KashierPaymentData ? $paymentData->toArray() : [],
            ]);
        } catch (Exception $e) {
            Log::error('Error showing Kashier payment: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('orders.index')
                ->with('error', 'Unable to process payment. Please try again later.');
        }
    }
}
