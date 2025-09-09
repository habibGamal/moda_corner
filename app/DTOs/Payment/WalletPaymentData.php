<?php

namespace App\DTOs\Payment;

class WalletPaymentData
{
    public function __construct(
        public readonly string $mobilePhone,
        public readonly ?string $intentionId = null,
        public readonly ?array $additionalData = null,
    ) {}

    /**
     * Create from array data
     */
    public static function fromRequestArray(array $data): self
    {
        return new self(
            mobilePhone: $data['mobile_phone'] ?? '',
            intentionId: $data['intention_id'] ?? null,
            additionalData: $data['additional_data'] ?? null,
        );
    }

    /**
     * Validate required fields for wallet payment
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->mobilePhone)) {
            $errors[] = 'Mobile phone is required for wallet payment';
        } elseif (! preg_match('/^01[0-9]{9}$/', $this->mobilePhone)) {
            $errors[] = 'Mobile phone must be a valid Egyptian mobile number (01xxxxxxxxx)';
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
     * Convert to array format
     */
    public function toArray(): array
    {
        return [
            'mobilePhone' => $this->mobilePhone,
            'intentionId' => $this->intentionId,
            'additionalData' => $this->additionalData,
        ];
    }
}
