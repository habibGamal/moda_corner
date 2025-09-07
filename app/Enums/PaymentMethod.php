<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasColor, HasIcon, HasLabel
{
    case CASH_ON_DELIVERY = 'cash_on_delivery';
    case CREDIT_CARD = 'credit_card';
    case KASHIER = 'kashier';

    public function getColor(): ?string
    {
        return match ($this) {
            self::CASH_ON_DELIVERY => 'gray',
            self::CREDIT_CARD => 'primary',
            self::KASHIER => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::CASH_ON_DELIVERY => 'heroicon-o-banknotes',
            self::CREDIT_CARD => 'heroicon-o-credit-card',
            self::KASHIER => 'heroicon-o-credit-card',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::CASH_ON_DELIVERY => 'الدفع عند الاستلام',
            self::CREDIT_CARD => 'بطاقة ائتمانية',
            self::KASHIER => 'الدفع الإلكتروني (كاشير)',
        };
    }

    public function isCOD(): bool
    {
        return $this === self::CASH_ON_DELIVERY;
    }

    public static function toSelectArray(): array
    {
        return [
            self::CASH_ON_DELIVERY->value => self::CASH_ON_DELIVERY->getLabel(),
            self::KASHIER->value => self::KASHIER->getLabel(),
            self::CREDIT_CARD->value => self::CREDIT_CARD->getLabel(),
        ];
    }
}
