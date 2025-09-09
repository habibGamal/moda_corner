<?php

namespace App\Http\Controllers;

use App\DTOs\PaymentResultData;
use App\Enums\PaymentMethod;
use App\Events\Payment\PaymentFailed;
use App\Events\Payment\PaymentInitiated;
use App\Events\Payment\PaymentSucceeded;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\Payment\PaymentProcessor;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

/**
 * Refactored payment controller following SOLID principles
 * Single Responsibility: Only handles HTTP requests/responses and delegates business logic
 */
class PaymentController extends Controller
{
    public function __construct(
        private PaymentProcessor $paymentProcessor,
        private OrderService $orderService
    ) {}

    /**
     * Initiate payment for an order
     */
    public function initiatePayment(Request $request)
    {
        try {
            $orderId = $request->input('order_id');
            $order = $this->orderService->getOrderById($orderId);

            $this->validatePaymentInitiation($order);

            $paymentData = $this->paymentProcessor->processPayment($order);

            // Store order ID in session for callbacks
            session()->put('payment_order_id', $order->id);

            // Fire payment initiated event
            PaymentInitiated::dispatch($order, $paymentData->toArray());

            return $this->renderPaymentPage($order, $paymentData);

        } catch (Exception $e) {
            Log::error('Error initializing payment: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->route('orders.index')
                ->with('error', 'Unable to process payment. Please try again later or contact support.');
        }
    }

    /**
     * Handle successful payment callback
     */
    public function handleSuccess(Request $request)
    {
        try {
            $orderId = session()->get('payment_order_id');

            if (! $orderId) {
                Log::error('Order ID not found in session', ['params' => $request->all()]);

                return redirect()->route('checkout.index')
                    ->with('error', 'Payment information not found. Please try again.');
            }

            $order = $this->orderService->getOrderById($orderId);

            // Check if payment is already processed (e.g., via webhook)
            if ($order->payment_status === \App\Enums\PaymentStatus::PAID) {
                Log::info('Payment already processed for order', [
                    'order_id' => $order->id,
                    'payment_status' => $order->payment_status->value,
                ]);

                // Clear session data
                session()->forget('payment_order_id');

                return redirect()->route('orders.show', $order->id)
                    ->with('success', 'Payment completed successfully! Your order is being processed.');
            }

            // Only process payment if not already paid
            $paymentData = $request->all();
            $updatedOrder = $this->paymentProcessor->processPaymentSuccess($order, $paymentData);

            // Clear session data
            session()->forget('payment_order_id');

            // Fire payment succeeded event
            PaymentSucceeded::dispatch($updatedOrder, $paymentData);

            return redirect()->route('orders.show', $updatedOrder->id)
                ->with('success', 'Payment completed successfully! Your order is being processed.');

        } catch (Exception $e) {
            Log::error('Error handling payment success: '.$e->getMessage(), [
                'exception' => $e,
                'params' => $request->all(),
            ]);

            return redirect()->route('checkout.index')
                ->with('error', 'Error processing your payment. Please try again or contact support.');
        }
    }

    /**
     * Handle failed payment callback
     */
    public function handleFailure(Request $request)
    {
        try {
            $orderId = session()->get('payment_order_id');
            session()->forget('payment_order_id');

            Log::info('Payment failed', [
                'params' => $request->all(),
                'orderId' => $orderId,
            ]);

            if ($orderId) {
                $order = $this->orderService->getOrderById($orderId);
                $paymentData = $request->all();

                $updatedOrder = $this->paymentProcessor->processPaymentFailure($order, $paymentData);

                // Fire payment failed event
                PaymentFailed::dispatch($updatedOrder, $paymentData, 'Payment gateway reported failure');
            }

            return redirect()->route('checkout.index')
                ->with('error', 'Payment was not successful. Please try again or use a different payment method.');

        } catch (Exception $e) {
            Log::error('Error handling payment failure: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->route('checkout.index')
                ->with('error', 'Error processing your payment. Please try again or contact support.');
        }
    }

    /**
     * Show payment page for an existing order
     */
    public function showPayment(int $orderId)
    {
        try {
            $order = $this->orderService->getOrderById($orderId);

            $this->validatePaymentInitiation($order);

            $paymentData = $this->paymentProcessor->processPayment($order);

            session()->put('payment_order_id', $order->id);

            return $this->renderPaymentPage($order, $paymentData);

        } catch (Exception $e) {
            Log::error('Error showing payment: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->route('orders.index')
                ->with('error', 'Unable to process payment. Please try again later.');
        }
    }

    /**
     * Validate that payment can be initiated for this order
     */
    private function validatePaymentInitiation(Order $order): void
    {
        if ($order->payment_status->isPaid()) {
            throw new \InvalidArgumentException('This order has already been paid.');
        }

        if (! $this->paymentProcessor->supportsPaymentMethod($order->payment_method)) {
            throw new \InvalidArgumentException('Payment method not supported.');
        }
    }

    /**
     * Render the appropriate payment page based on payment method
     */
    private function renderPaymentPage(Order $order, PaymentResultData $paymentData)
    {
        return match ($order->payment_method) {
            PaymentMethod::CREDIT_CARD, PaymentMethod::WALLET => Inertia::render('Payments/Kashier', [
                'kashierParams' => $paymentData->toArray(),
                'order' => $order,
            ]),
            PaymentMethod::CASH_ON_DELIVERY => redirect()->route('orders.show', $order->id)
                ->with('info', 'Your order has been placed. Payment will be collected upon delivery.'),
            default => redirect()->route('orders.index')
                ->with('error', 'Unsupported payment method.')
        };
    }
}
