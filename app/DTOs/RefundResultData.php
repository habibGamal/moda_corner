<?php

namespace App\DTOs;

class RefundResultData
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $transactionId = null,
        public readonly ?string $cardOrderId = null,
        public readonly ?string $orderReference = null,
        public readonly ?string $gatewayCode = null,
        public readonly ?string $gatewayMessage = null,
        public readonly ?string $transactionResponseCode = null,
        public readonly ?string $transactionDate = null,
        public readonly ?string $settlementDate = null,
        public readonly ?float $amount = null,
        public readonly ?string $currency = null,
        public readonly ?string $operation = null,
        public readonly ?string $merchantIdentifier = null,
        public readonly ?string $creationDate = null,
        public readonly ?string $messageEn = null,
        public readonly ?string $messageAr = null,
        public readonly ?string $transactionResponseMessageEn = null,
        public readonly ?string $transactionResponseMessageAr = null,
        public readonly ?string $orderStatus = null,
        public readonly ?float $totalRefundedAmount = null,
        public readonly ?array $fullResponse = null
    ) {}

    /**
     * Create a successful refund result from API response
     */
    public static function success(array $responseData): self
    {
        return new self(
            success: true,
            transactionId: $responseData['response']['transactionId'] ?? null,
            cardOrderId: $responseData['response']['cardOrderId'] ?? null,
            orderReference: $responseData['response']['orderReference'] ?? null,
            gatewayCode: $responseData['response']['gatewayCode'] ?? null,
            gatewayMessage: $responseData['response']['gatewayMessage'] ?? null,
            transactionResponseCode: $responseData['response']['transactionResponseCode'] ?? null,
            transactionDate: $responseData['response']['transactionDate'] ?? null,
            settlementDate: $responseData['response']['settlementDate'] ?? null,
            amount: $responseData['response']['amount'] ?? null,
            currency: $responseData['response']['currency'] ?? 'EGP',
            operation: $responseData['response']['operation'] ?? 'refund',
            merchantIdentifier: $responseData['response']['merchantIdentifier'] ?? null,
            creationDate: $responseData['response']['creationDate'] ?? null,
            messageEn: $responseData['messages']['en'] ?? 'Refund processed successfully',
            messageAr: $responseData['messages']['ar'] ?? 'تم معالجة الاسترداد بنجاح',
            transactionResponseMessageEn: $responseData['response']['transactionResponseMessage']['en'] ?? null,
            transactionResponseMessageAr: $responseData['response']['transactionResponseMessage']['ar'] ?? null,
            orderStatus: $responseData['response']['payload']['order']['status'] ?? null,
            totalRefundedAmount: $responseData['response']['payload']['order']['totalRefundedAmount'] ?? null,
            fullResponse: $responseData
        );
    }

    /**
     * Create a failed refund result from API response
     */
    public static function failure(array $responseData): self
    {
        $errorMessage = $responseData['messages']['en'] ??
                       $responseData['response']['transactionResponseMessage']['en'] ??
                       $responseData['response']['gatewayMessage'] ??
                       'Unknown error occurred';

        return new self(
            success: false,
            gatewayCode: $responseData['response']['gatewayCode'] ?? null,
            gatewayMessage: $responseData['response']['gatewayMessage'] ?? null,
            transactionResponseCode: $responseData['response']['transactionResponseCode'] ?? null,
            messageEn: $errorMessage,
            messageAr: $responseData['messages']['ar'] ?? 'فشل في معالجة الاسترداد',
            transactionResponseMessageEn: $responseData['response']['transactionResponseMessage']['en'] ?? null,
            transactionResponseMessageAr: $responseData['response']['transactionResponseMessage']['ar'] ?? null,
            fullResponse: $responseData
        );
    }

    /**
     * Create a failure result from exception
     */
    public static function exception(string $message): self
    {
        return new self(
            success: false,
            messageEn: "Failed to process refund: {$message}"
        );
    }

    /**
     * Convert to array format for backward compatibility
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'transaction_id' => $this->transactionId,
            'card_order_id' => $this->cardOrderId,
            'order_reference' => $this->orderReference,
            'gateway_code' => $this->gatewayCode,
            'gateway_message' => $this->gatewayMessage,
            'transaction_response_code' => $this->transactionResponseCode,
            'transaction_date' => $this->transactionDate,
            'settlement_date' => $this->settlementDate,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'operation' => $this->operation,
            'merchant_identifier' => $this->merchantIdentifier,
            'creation_date' => $this->creationDate,
            'message_en' => $this->messageEn,
            'message_ar' => $this->messageAr,
            'transaction_response_message_en' => $this->transactionResponseMessageEn,
            'transaction_response_message_ar' => $this->transactionResponseMessageAr,
            'order_status' => $this->orderStatus,
            'total_refunded_amount' => $this->totalRefundedAmount,
            'full_response' => $this->fullResponse,
            // Legacy keys for backward compatibility
            'message' => $this->messageEn,
        ];
    }
}
