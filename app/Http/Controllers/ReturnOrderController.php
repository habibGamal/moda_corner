<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestReturnOrderRequest;
use App\Models\Order;
use App\Models\ReturnOrder;
use App\Services\OrderService;
use App\Services\ReturnOrderService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ReturnOrderController extends Controller
{
    protected ReturnOrderService $returnOrderService;

    protected OrderService $orderService;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        ReturnOrderService $returnOrderService,
        OrderService $orderService
    ) {
        $this->returnOrderService = $returnOrderService;
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of the user's return orders.
     */
    public function index(Request $request)
    {
        $returnOrders = ReturnOrder::where('user_id', Auth::id())
            ->with(
                'order.user',
                'order.shippingAddress',
                'returnItems.orderItem.product',
                'returnItems.orderItem.variant',)
            ->orderBy('created_at', 'desc')->get();

        return Inertia::render('Returns/Index', [
            'returnOrders' => $returnOrders,
        ]);
    }

    /**
     * Show the form for creating a new return order.
     */
    public function create(int $orderId)
    {
        try {
            $order = Order::where('user_id', Auth::id())
                ->with(['items.product', 'items.variant'])
                ->findOrFail($orderId);

            if (! $this->returnOrderService->isOrderEligibleForReturn($order)) {
                return redirect()->route('orders.show', $order->id)
                    ->with('error', 'This order is not eligible for return.');
            }

            // Get items that can still be returned (considering existing returns)
            $returnableItems = [];
            foreach ($order->items as $item) {
                $maxReturnableQuantity = $this->returnOrderService->getMaxReturnableQuantity($item);
                if ($maxReturnableQuantity > 0) {
                    $returnableItems[] = [
                        'id' => $item->id,
                        'product' => $item->product,
                        'variant' => $item->variant,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'subtotal' => $item->subtotal,
                        'max_returnable_quantity' => $maxReturnableQuantity,
                    ];
                }
            }

            if (empty($returnableItems)) {
                return redirect()->route('orders.show', $order->id)
                    ->with('error', 'No items are eligible for return in this order.');
            }

            return Inertia::render('Returns/Create', [
                'order' => $order,
                'returnableItems' => $returnableItems,
            ]);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('orders.index')
                ->with('error', 'Order not found.');
        } catch (Exception $e) {
            Log::error('Failed to load return order creation page', [
                'order_id' => $orderId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('orders.index')
                ->with('error', 'Unable to load return order page.');
        }
    }

    /**
     * Store a newly created return order.
     */
    public function store(RequestReturnOrderRequest $request, int $orderId)
    {
        try {
            $validated = $request->validated();

            $returnOrder = $this->returnOrderService->requestReturn(
                $orderId,
                $validated['return_items'],
                $validated['reason']
            );

            return redirect()->route('returns.show', $returnOrder->return_number)
                ->with('success', 'Return request submitted successfully. It will be reviewed by our team.');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('orders.index')
                ->with('error', 'Order not found.');
        } catch (Exception $e) {
            Log::error('Return request failed', [
                'order_id' => $orderId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified return order.
     */
    public function show(string $returnNumber)
    {
        try {
            $returnOrder = $this->returnOrderService->getByReturnNumber($returnNumber);

            if (! $returnOrder || $returnOrder->user_id !== Auth::id()) {
                return redirect()->route('returns.index')
                    ->with('error', 'Return order not found.');
            }

            return Inertia::render('Returns/Show', [
                'returnOrder' => $returnOrder,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to show return order', [
                'return_number' => $returnNumber,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('returns.index')
                ->with('error', 'Unable to load return order details.');
        }
    }

    /**
     * Show return history for the user (legacy support)
     */
    public function history(Request $request)
    {
        return $this->index($request);
    }
}
