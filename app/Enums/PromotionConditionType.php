<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PromotionConditionType: string implements HasColor, HasIcon, HasLabel
{
    case PRODUCT = 'product';
    case CATEGORY = 'category';
    case BRAND = 'brand';
    case CUSTOMER = 'customer';

    public function getColor(): ?string
    {
        return match ($this) {
            self::PRODUCT => 'primary',
            self::CATEGORY => 'success',
            self::BRAND => 'warning',
            self::CUSTOMER => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PRODUCT => 'heroicon-o-shopping-bag',
            self::CATEGORY => 'heroicon-o-tag',
            self::BRAND => 'heroicon-o-bookmark',
            self::CUSTOMER => 'heroicon-o-user',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PRODUCT => 'منتج محدد',
            self::CATEGORY => 'فئة محددة',
            self::BRAND => 'علامة تجارية محددة',
            self::CUSTOMER => 'عميل محدد',
        };
    }

    public static function toSelectArray(): array
    {
        return [
            self::PRODUCT->value => self::PRODUCT->getLabel(),
            self::CATEGORY->value => self::CATEGORY->getLabel(),
            self::BRAND->value => self::BRAND->getLabel(),
            self::CUSTOMER->value => self::CUSTOMER->getLabel(),
        ];
    }
}
