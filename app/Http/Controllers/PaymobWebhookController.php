<?php

namespace App\Http\Controllers;

use App\Services\Payment\Webhooks\PaymobWebhookHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controller for handling Paymob payment webhooks
 * Follows Single Responsibility Principle - only handles HTTP and delegates to handler
 */
class PaymobWebhookController extends Controller
{
    public function __construct(
        private PaymobWebhookHandler $webhookHandler
    ) {}

    /**
     * Handle incoming webhook from Paymob
     */
    public function handle(Request $request)
    {
        Log::info('Paymob webhook endpoint hit', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'headers' => $request->headers->all(),
        ]);

        $result = $this->webhookHandler->handle($request);

        $statusCode = $result['status'] === 'success' ? 200 : 400;

        return response()->json($result, $statusCode);
    }
}
