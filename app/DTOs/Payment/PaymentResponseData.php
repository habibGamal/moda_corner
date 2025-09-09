<?php

namespace App\DTOs\Payment;

use App\Models\Order;

class PaymentResponseData
{
    public function __construct(
        public readonly bool $success,
        public readonly Order $order,
        public readonly ?string $transactionId = null,
        public readonly ?string $paymentId = null,
        public readonly ?array $paymentDetails = null,
        public readonly ?string $errorMessage = null,
        public readonly ?array $rawData = null,
    ) {}

    /**
     * Create a successful payment response
     */
    public static function success(
        Order $order,
        string $transactionId,
        ?string $paymentId = null,
        ?array $paymentDetails = null,
        ?array $rawData = null
    ): self {
        return new self(
            success: true,
            order: $order,
            transactionId: $transactionId,
            paymentId: $paymentId,
            paymentDetails: $paymentDetails,
            rawData: $rawData
        );
    }

    /**
     * Create a failed payment response
     */
    public static function failure(
        Order $order,
        string $errorMessage,
        ?array $rawData = null
    ): self {
        return new self(
            success: false,
            order: $order,
            errorMessage: $errorMessage,
            rawData: $rawData
        );
    }

    /**
     * Convert to array format
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'order_id' => $this->order->id,
            'transaction_id' => $this->transactionId,
            'payment_id' => $this->paymentId,
            'payment_details' => $this->paymentDetails,
            'error_message' => $this->errorMessage,
            'raw_data' => $this->rawData,
        ];
    }
}
