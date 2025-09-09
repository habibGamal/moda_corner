<?php

namespace App\DTOs\Payment;

class CardPaymentData
{
    public function __construct(
        public readonly string $cardNumber,
        public readonly string $expiryMonth,
        public readonly string $expiryYear,
        public readonly string $securityCode,
        public readonly string $nameOnCard,
        public readonly bool $saveCard = false,
        public readonly ?string $intentionId = null,
        public readonly ?array $additionalData = null,
    ) {}

    /**
     * Create from array data
     */
    public static function fromRequestArray(array $data): self
    {
        return new self(
            cardNumber: $data['card_number'] ?? '',
            expiryMonth: $data['expiry_month'] ?? '',
            expiryYear: $data['expiry_year'] ?? '',
            securityCode: $data['security_code'] ?? '',
            nameOnCard: $data['name_on_card'] ?? '',
            saveCard: $data['save_card'] ?? false,
            intentionId: $data['intention_id'] ?? null,
            additionalData: $data['additional_data'] ?? null,
        );
    }

    /**
     * Validate required fields for card payment
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->cardNumber)) {
            $errors[] = 'Card number is required';
        }

        if (empty($this->expiryMonth)) {
            $errors[] = 'Expiry month is required';
        } elseif (! is_numeric($this->expiryMonth) || (int) $this->expiryMonth < 1 || (int) $this->expiryMonth > 12) {
            $errors[] = 'Expiry month must be between 1 and 12';
        }

        if (empty($this->expiryYear)) {
            $errors[] = 'Expiry year is required';
        } elseif (! is_numeric($this->expiryYear) || strlen($this->expiryYear) < 2) {
            $errors[] = 'Expiry year must be a valid year';
        }

        if (empty($this->securityCode)) {
            $errors[] = 'Security code is required';
        } elseif (! is_numeric($this->securityCode) || strlen($this->securityCode) < 3 || strlen($this->securityCode) > 4) {
            $errors[] = 'Security code must be 3 or 4 digits';
        }

        if (empty($this->nameOnCard)) {
            $errors[] = 'Name on card is required';
        }

        return $errors;
    }

    /**
     * Check if the payment data is valid
     */
    public function isValid(): bool
    {
        return empty($this->validate());
    }

    /**
     * Get formatted expiry month (with leading zero)
     */
    public function getFormattedExpiryMonth(): string
    {
        return str_pad($this->expiryMonth, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Convert to array format
     */
    public function toArray(): array
    {
        return [
            'cardNumber' => $this->cardNumber,
            'expiryMonth' => $this->expiryMonth,
            'expiryYear' => $this->expiryYear,
            'securityCode' => $this->securityCode,
            'nameOnCard' => $this->nameOnCard,
            'saveCard' => $this->saveCard,
            'intentionId' => $this->intentionId,
            'additionalData' => $this->additionalData,
        ];
    }
}
