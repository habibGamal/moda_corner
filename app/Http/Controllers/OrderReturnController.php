<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestOrderReturnRequest;
use App\Services\OrderReturnService;
use App\Services\OrderService; // New service
use App\Services\ReturnOrderService;
use Illuminate\Http\Request;

class OrderReturnController extends Controller
{
    protected OrderReturnService $orderReturnService; // Legacy service

    protected ReturnOrderService $returnOrderService; // New service

    protected OrderService $orderService;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        OrderReturnService $orderReturnService,
        ReturnOrderService $returnOrderService,
        OrderService $orderService
    ) {
        $this->orderReturnService = $orderReturnService;
        $this->returnOrderService = $returnOrderService;
        $this->orderService = $orderService;
    }

    /**
     * Request return for an order (Legacy - redirects to new return system)
     */
    public function requestReturn(RequestOrderReturnRequest $request, int $orderId)
    {
        // Redirect to the new return system
        return redirect()->route('returns.create', $orderId)
            ->with('info', 'Please use the new return system to create your return request.');
    }

    /**
     * Show return history for the user (Legacy - redirects to new system)
     */
    public function history(Request $request)
    {
        // Redirect to the new return system
        return redirect()->route('returns.index')
            ->with('info', 'You have been redirected to the new return management system.');
    }
}
