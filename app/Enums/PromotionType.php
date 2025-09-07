<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PromotionType: string implements HasColor, HasIcon, HasLabel
{
    case PERCENTAGE = 'percentage';
    case FIXED = 'fixed';
    case FREE_SHIPPING = 'free_shipping';
    case BUY_X_GET_Y = 'buy_x_get_y';

    public function getColor(): ?string
    {
        return match ($this) {
            self::PERCENTAGE => 'success',
            self::FIXED => 'warning',
            self::FREE_SHIPPING => 'info',
            self::BUY_X_GET_Y => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PERCENTAGE => 'heroicon-o-currency-dollar',
            self::FIXED => 'heroicon-o-banknotes',
            self::FREE_SHIPPING => 'heroicon-o-truck',
            self::BUY_X_GET_Y => 'heroicon-o-gift',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PERCENTAGE => 'خصم نسبة مئوية',
            self::FIXED => 'خصم مبلغ ثابت',
            self::FREE_SHIPPING => 'شحن مجاني',
            self::BUY_X_GET_Y => 'اشتري واحصل',
        };
    }

    public static function toSelectArray(): array
    {
        return [
            self::PERCENTAGE->value => self::PERCENTAGE->getLabel(),
            self::FIXED->value => self::FIXED->getLabel(),
            self::FREE_SHIPPING->value => self::FREE_SHIPPING->getLabel(),
            self::BUY_X_GET_Y->value => self::BUY_X_GET_Y->getLabel(),
        ];
    }
}
