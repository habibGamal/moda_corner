<?php

namespace App\Http\Controllers;

use App\Services\Payment\Webhooks\PaymentWebhookHandler;
use Illuminate\Http\Request;

/**
 * Simple webhook controller that delegates to the webhook handler
 * Follows Single Responsibility Principle
 */
class PaymentWebhookController extends Controller
{
    public function __construct(
        private PaymentWebhookHandler $webhookHandler
    ) {}

    /**
     * Handle payment webhook
     */
    public function handle(Request $request)
    {
        $result = $this->webhookHandler->handle($request);

        $statusCode = $result['status'] === 'success' ? 200 :
                     ($result['status'] === 'error' && str_contains($result['message'], 'not found') ? 404 : 400);

        return response()->json($result, $statusCode);
    }
}
