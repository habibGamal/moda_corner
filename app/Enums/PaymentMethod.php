<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasColor, HasIcon, HasLabel
{
    case CASH_ON_DELIVERY = 'cash_on_delivery';
    case CARD = 'card';
    case WALLET = 'wallet';

    public function getColor(): ?string
    {
        return match ($this) {
            self::CASH_ON_DELIVERY => 'gray',
            self::CARD => 'primary',
            self::WALLET => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::CASH_ON_DELIVERY => 'heroicon-o-banknotes',
            self::CARD => 'heroicon-o-credit-card',
            self::WALLET => 'heroicon-o-wallet',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::CASH_ON_DELIVERY => 'الدفع عند الاستلام',
            self::CARD => 'بطاقة ائتمانية',
            self::WALLET => 'محفظة إلكترونية',
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
            self::CARD->value => self::CARD->getLabel(),
            self::WALLET->value => self::WALLET->getLabel(),
        ];
    }
}
