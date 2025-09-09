<?php

namespace App\DTOs\Payment;

class PaymentValidationData
{
    public function __construct(
        public readonly bool $isValid,
        public readonly ?string $transactionId = null,
        public readonly ?string $orderId = null,
        public readonly ?string $amount = null,
        public readonly ?string $currency = null,
        public readonly ?string $status = null,
        public readonly ?string $errorMessage = null,
        public readonly ?array $rawData = null,
    ) {}

    /**
     * Create a valid payment validation result
     */
    public static function valid(
        string $transactionId,
        string $orderId,
        string $amount,
        string $currency,
        string $status,
        array $rawData = []
    ): self {
        return new self(
            isValid: true,
            transactionId: $transactionId,
            orderId: $orderId,
            amount: $amount,
            currency: $currency,
            status: $status,
            rawData: $rawData
        );
    }

    /**
     * Create an invalid payment validation result
     */
    public static function invalid(string $errorMessage, array $rawData = []): self
    {
        return new self(
            isValid: false,
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
            'is_valid' => $this->isValid,
            'transaction_id' => $this->transactionId,
            'order_id' => $this->orderId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'error_message' => $this->errorMessage,
            'raw_data' => $this->rawData,
        ];
    }
}
