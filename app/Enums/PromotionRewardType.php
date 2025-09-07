<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PromotionRewardType: string implements HasColor, HasIcon, HasLabel
{
    case PRODUCT = 'product';
    case CATEGORY = 'category';
    case BRAND = 'brand';

    public function getColor(): ?string
    {
        return match ($this) {
            self::PRODUCT => 'primary',
            self::CATEGORY => 'success',
            self::BRAND => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PRODUCT => 'heroicon-o-gift',
            self::CATEGORY => 'heroicon-o-tag',
            self::BRAND => 'heroicon-o-bookmark',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PRODUCT => 'منتج محدد',
            self::CATEGORY => 'فئة محددة',
            self::BRAND => 'علامة تجارية محددة',
        };
    }

    public static function toSelectArray(): array
    {
        return [
            self::PRODUCT->value => self::PRODUCT->getLabel(),
            self::CATEGORY->value => self::CATEGORY->getLabel(),
            self::BRAND->value => self::BRAND->getLabel(),
        ];
    }
}
