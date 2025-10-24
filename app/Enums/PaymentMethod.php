<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasColor, HasIcon, HasLabel
{
    case CASH_ON_DELIVERY = 'cash_on_delivery';
    case CREDIT_CARD = 'credit_card';
    case WALLET = 'wallet';
    case INSTAPAY = 'instapay';

    public function getColor(): ?string
    {
        return match ($this) {
            self::CASH_ON_DELIVERY => 'gray',
            self::CREDIT_CARD => 'primary',
            self::WALLET => 'success',
            self::INSTAPAY => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::CASH_ON_DELIVERY => 'heroicon-o-banknotes',
            self::CREDIT_CARD => 'heroicon-o-credit-card',
            self::WALLET => 'heroicon-o-wallet',
            self::INSTAPAY => 'heroicon-o-building-library',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::CASH_ON_DELIVERY => 'الدفع عند الاستلام',
            self::CREDIT_CARD => 'بطاقة ائتمانية',
            self::WALLET => 'المحفظة الإلكترونية',
            self::INSTAPAY => 'إنستاباي',
        };
    }

    public function isCOD(): bool
    {
        return $this === self::CASH_ON_DELIVERY;
    }

    public function requiresOnlineGateway(): bool
    {
        return $this === self::CREDIT_CARD || $this === self::WALLET;
    }

    public function requiresManualVerification(): bool
    {
        return $this === self::INSTAPAY;
    }

    public static function toSelectArray(): array
    {
        return [
            self::CASH_ON_DELIVERY->value => self::CASH_ON_DELIVERY->getLabel(),
            self::CREDIT_CARD->value => self::CREDIT_CARD->getLabel(),
            self::WALLET->value => self::WALLET->getLabel(),
            self::INSTAPAY->value => self::INSTAPAY->getLabel(),
        ];
    }
}
