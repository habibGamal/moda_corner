<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasColor, HasIcon, HasLabel
{
    /**
     * Pending status used for the cash-on-delivery payment method.
     * This status indicates that the payment has not yet been completed.
     * After the order is delivered, the status will be updated to PAID.
     */
    case PENDING = 'pending';
    case PAID = 'paid';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';

    public function getColor(): ?string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PAID => 'success',
            self::FAILED => 'danger',
            self::REFUNDED => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PENDING => 'heroicon-o-clock',
            self::PAID => 'heroicon-o-check-circle',
            self::FAILED => 'heroicon-o-x-circle',
            self::REFUNDED => 'heroicon-o-arrow-uturn-left',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'قيد الانتظار',
            self::PAID => 'تم الدفع',
            self::FAILED => 'فشل الدفع',
            self::REFUNDED => 'تم الاسترداد',
        };
    }

    public function isPaid(): bool
    {
        return $this === self::PAID;
    }

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    public function isFailed(): bool
    {
        return $this === self::FAILED;
    }

    public function isRefunded(): bool
    {
        return $this === self::REFUNDED;
    }

    public static function toSelectArray(): array
    {
        return [
            self::PENDING->value => self::PENDING->getLabel(),
            self::PAID->value => self::PAID->getLabel(),
            self::FAILED->value => self::FAILED->getLabel(),
            self::REFUNDED->value => self::REFUNDED->getLabel(),
        ];
    }
}
