<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasColor, HasIcon, HasLabel
{
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function getColor(): ?string
    {
        return match ($this) {
            self::PROCESSING => 'warning',
            self::SHIPPED => 'info',
            self::DELIVERED => 'success',
            self::CANCELLED => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PROCESSING => 'heroicon-o-cog',
            self::SHIPPED => 'heroicon-o-truck',
            self::DELIVERED => 'heroicon-o-check-circle',
            self::CANCELLED => 'heroicon-o-x-circle',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PROCESSING => 'قيد التحضير',
            self::SHIPPED => 'تم الشحن',
            self::DELIVERED => 'تم التوصيل',
            self::CANCELLED => 'ملغاة',
        };
    }

    public static function toSelectArray(): array
    {
        return [
            self::PROCESSING->value => self::PROCESSING->getLabel(),
            self::SHIPPED->value => self::SHIPPED->getLabel(),
            self::DELIVERED->value => self::DELIVERED->getLabel(),
            self::CANCELLED->value => self::CANCELLED->getLabel(),
        ];
    }
}
