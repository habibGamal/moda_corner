<?php

namespace App\Http\Controllers;

use App\DTOs\CartSummaryData;
use App\DTOs\OrderPlacementData;
use App\DTOs\Payment\CardPaymentData;
use App\DTOs\Payment\WalletPaymentData;
use App\Enums\PaymentMethod;
use App\Http\Requests\StoreOrderRequest;
use App\Interfaces\PaymentGatewayFactoryInterface;
use App\Models\Address;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderCancellationService;
use App\Services\OrderEvaluationService;
use App\Services\OrderReturnService;
use App\Services\OrderService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class OrderController extends Controller
{
    protected OrderService $orderService;

    protected CartService $cartService;

    protected OrderEvaluationService $orderEvaluationService;

    protected OrderReturnService $orderReturnService;

    protected OrderCancellationService $orderCancellationService;

    protected PaymentGatewayFactoryInterface $gatewayFactory;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        OrderService $orderService,
        CartService $cartService,
        OrderEvaluationService $orderEvaluationService,
        OrderReturnService $orderReturnService,
        OrderCancellationService $orderCancellationService,
        PaymentGatewayFactoryInterface $gatewayFactory
    ) {
        $this->orderService = $orderService;
        $this->cartService = $cartService;
        $this->orderEvaluationService = $orderEvaluationService;
        $this->orderReturnService = $orderReturnService;
        $this->orderCancellationService = $orderCancellationService;
        $this->gatewayFactory = $gatewayFactory;
        // Note: Middleware is typically applied in routes/web.php for better clarity
    }

    /**
     * Display a listing of the user's orders.
     *
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $query = $this->orderService->getUserOrders($request->input('limit', 10));

        return Inertia::render('Orders/Index', [
            'orders_data' => inertia()->merge(
                $query->items()
            ),
            'orders_pagination' => [
                'current_page' => $query->currentPage(),
                'last_page' => $query->lastPage(),
                'per_page' => $query->perPage(),
                'total' => $query->total(),
                'has_more_pages' => $query->hasMorePages(),
            ],
        ]);
    }

    /**
     * Display the specified order.
     *
     * @return \Inertia\Response|\Illuminate\Http\RedirectResponse
     */
    public function show(int $orderId)
    {
        try {
            $order = $this->orderService->getOrderById($orderId);
            $canRequestReturn = $this->orderReturnService->isOrderEligibleForReturn($order);

            return Inertia::render('Orders/Show', [
                'order' => $order,
                'canRequestReturn' => $canRequestReturn,
            ]);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('orders.index')->with('error', 'Order not found.');
        }
    }

    /**
     * Show the checkout page.
     *
     * @return \Inertia\Response|\Illuminate\Http\RedirectResponse
     */
    public function checkout()
    {
        $user = Auth::user();
        $addresses = Address::where('user_id', $user->id)->with('area.gov')->get();
        $selectedAddressId = request()->input('address_id');
        $couponCode = request()->input('coupon_code');
        $cart = $this->cartService->getCart();
        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty. Please add items to your cart before checking out.');
        }

        if ($selectedAddressId) {
            // Use the OrderEvaluationService to get the order total
            $evaluation = $this->orderEvaluationService->evaluateOrderCalculation($selectedAddressId, $couponCode);
            $orderSummary = [
                'subtotal' => $evaluation->subtotal,
                'shippingCost' => $evaluation->finalShippingCost,
                'discount' => $evaluation->discount,
                'total' => $evaluation->total,
                'shippingDiscount' => $evaluation->shippingDiscount,
                'appliedPromotion' => $evaluation->appliedPromotion,
            ];
        } else {
            $orderSummary = null;
        }

        return Inertia::render('Checkout/Index', [
            'addresses' => $addresses,
            'orderSummary' => $orderSummary,
            // Convert CartSummaryData to array for frontend
            'cartSummary' => [
                'totalItems' => $this->cartService->getCartSummary()->totalItems,
                'totalPrice' => $this->cartService->getCartSummary()->totalPrice,
            ],
            // Pass allowed payment methods
            'paymentMethods' => ['cash_on_delivery', 'card', 'wallet'],
            'defaultPaymentMethod' => 'cash_on_delivery',
        ]);
    }

    /**
     * Handle order submission from checkout
     *
     * @return \Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function store(StoreOrderRequest $request)
    {
        try {
            $orderData = new OrderPlacementData(
                addressId: $request->validated('address_id'),
                paymentMethod: PaymentMethod::from($request->validated('payment_method')),
                couponCode: $request->validated('coupon_code'),
                notes: $request->validated('notes')
            );
            \DB::transaction(function () use ($orderData, &$order, $request) {
                $order = $this->orderService->placeOrderFromCart($orderData);

                // Use PaymentGatewayFactory to handle different payment methods
                $paymentMethod = $request->validated('payment_method');

                if ($paymentMethod === PaymentMethod::CASH_ON_DELIVERY->value) {
                    return redirect()->route('orders.show', $order->id)
                        ->with('success', 'Order placed successfully!');
                }

                // For electronic payment methods, redirect to payment initiation
                $gateway = $this->gatewayFactory->createGateway('kashier'); // Default to Kashier for electronic payments

                if ($paymentMethod === PaymentMethod::CARD->value) {
                    // Initialize card payment
                    $paymentData = CardPaymentData::fromRequestArray($request->only([
                        'card_number',
                        'expiry_month',
                        'expiry_year',
                        'security_code',
                        'name_on_card',
                        'intention_id',
                        'additional_data',
                    ]));

                    // Process Card Payment
                    $gateway->payWithCard($order, $paymentData);
                } elseif ($paymentMethod === 'wallet') {
                    // Initialize wallet payment
                    $paymentData = WalletPaymentData::fromRequestArray($request->only([
                        'mobile_phone',
                        'intention_id',
                        'additional_data',
                    ]));

                    // Process Wallet Payment
                    $gateway->payWithWallet($order, $paymentData);
                }
            });

            // Fallback - should not happen with proper gateway implementation
            return redirect()->route('orders.show', $order->id)
                ->with('error', 'Payment gateway configuration error.');

        } catch (ModelNotFoundException $e) {
            Log::warning('Order placement failed: Address not found.', ['address_id' => $request->validated('address_id'), 'user_id' => Auth::id()]);

            return back()->withErrors('Selected address not found. Please select a valid address.');
        } catch (Exception $e) {
            Log::error('Order placement failed: ' . $e->getMessage(), ['exception' => $e, 'user_id' => Auth::id(), 'request_data' => $request->except(['_token'])]);
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return back()->withErrors($e->errors())->withInput();
            }
            return back()->withErrors('Failed to place order. Please try again later or contact support.');
        }
    }

    /**
     * Cancel the specified order.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(int $orderId)
    {
        try {
            // Ensure the order belongs to the authenticated user
            $order = Order::where('user_id', Auth::id())->findOrFail($orderId);
            $this->orderCancellationService->cancelOrder($order->id);

            return redirect()->route('orders.index')->with('success', 'Order cancelled successfully.');
        } catch (ModelNotFoundException $e) {
            Log::warning('Order cancellation failed: Order not found.', ['order_id' => $orderId, 'user_id' => Auth::id()]);

            return redirect()->route('orders.index')->with('error', 'Order not found.');
        } catch (Exception $e) {
            Log::error('Order cancellation failed: ' . $e->getMessage(), ['exception' => $e, 'order_id' => $orderId, 'user_id' => Auth::id()]);

            return back()->with('error', 'Failed to cancel order: ' . $e->getMessage());
        }
    }
}
