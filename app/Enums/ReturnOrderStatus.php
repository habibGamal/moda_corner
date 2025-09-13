<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ReturnOrderStatus: string implements HasColor, HasIcon, HasLabel
{
    case REQUESTED = 'requested';
    case APPROVED = 'approved';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';

    public function getColor(): ?string
    {
        return match ($this) {
            self::REQUESTED => 'warning',
            self::APPROVED => 'info',
            self::COMPLETED => 'success',
            self::REJECTED => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::REQUESTED => 'heroicon-o-clock',
            self::APPROVED => 'heroicon-o-check',
            self::COMPLETED => 'heroicon-o-check-circle',
            self::REJECTED => 'heroicon-o-x-mark',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::REQUESTED => 'مطلوب',
            self::APPROVED => 'معتمد',
            self::COMPLETED => 'مكتمل',
            self::REJECTED => 'مرفوض',
        };
    }

    public static function toSelectArray(): array
    {
        return [
            self::REQUESTED->value => self::REQUESTED->getLabel(),
            self::APPROVED->value => self::APPROVED->getLabel(),
            self::COMPLETED->value => self::COMPLETED->getLabel(),
            self::REJECTED->value => self::REJECTED->getLabel(),
        ];
    }
}
