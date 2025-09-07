<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestOrderReturnRequest;
use App\Services\OrderReturnService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class OrderReturnController extends Controller
{
    protected OrderReturnService $orderReturnService;
    protected OrderService $orderService;

    /**
     * Create a new controller instance.
     *
     * @param OrderReturnService $orderReturnService
     * @param OrderService $orderService
     */
    public function __construct(
        OrderReturnService $orderReturnService,
        OrderService $orderService
    ) {
        $this->orderReturnService = $orderReturnService;
        $this->orderService = $orderService;
    }

    /**
     * Request return for an order
     *
     * @param RequestOrderReturnRequest $request
     * @param int $orderId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function requestReturn(RequestOrderReturnRequest $request, int $orderId)
    {
        try {
            $order = $this->orderReturnService->requestReturn(
                $orderId,
                $request->validated('reason')
            );

            return redirect()->route('orders.show', $order->id)
                ->with('success', 'تم إرسال طلب الإرجاع بنجاح. سيتم مراجعته من قبل الإدارة.');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('orders.index')
                ->with('error', 'الطلب غير موجود.');
        } catch (Exception $e) {
            Log::error('Return request failed', [
                'order_id' => $orderId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('orders.show', $orderId)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show return history for the user
     *
     * @param Request $request
     * @return \Inertia\Response
     */
    public function history(Request $request)
    {
        $returnHistory = $this->orderReturnService->getUserReturnHistory();

        return Inertia::render('Orders/ReturnHistory', [
            'returnHistory' => $returnHistory,
        ]);
    }
}
