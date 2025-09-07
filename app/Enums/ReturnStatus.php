<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ReturnStatus: string implements HasColor, HasIcon, HasLabel
{
    case RETURN_REQUESTED = 'return_requested';
    case RETURN_APPROVED = 'return_approved';
    case RETURN_REJECTED = 'return_rejected';
    case ITEM_RETURNED = 'item_returned';
    case REFUND_PROCESSED = 'refund_processed';

    public function getColor(): ?string
    {
        return match ($this) {
            self::RETURN_REQUESTED => 'warning',
            self::RETURN_APPROVED => 'info',
            self::RETURN_REJECTED => 'danger',
            self::ITEM_RETURNED => 'success',
            self::REFUND_PROCESSED => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::RETURN_REQUESTED => 'heroicon-o-clock',
            self::RETURN_APPROVED => 'heroicon-o-check',
            self::RETURN_REJECTED => 'heroicon-o-x-mark',
            self::ITEM_RETURNED => 'heroicon-o-arrow-uturn-left',
            self::REFUND_PROCESSED => 'heroicon-o-banknotes',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::RETURN_REQUESTED => 'مطلوب إرجاع',
            self::RETURN_APPROVED => 'معتمد للإرجاع',
            self::RETURN_REJECTED => 'مرفوض الإرجاع',
            self::ITEM_RETURNED => 'تم الإرجاع',
            self::REFUND_PROCESSED => 'تم رد المبلغ',
        };
    }

    public static function toSelectArray(): array
    {
        return [
            self::RETURN_REQUESTED->value => self::RETURN_REQUESTED->getLabel(),
            self::RETURN_APPROVED->value => self::RETURN_APPROVED->getLabel(),
            self::RETURN_REJECTED->value => self::RETURN_REJECTED->getLabel(),
            self::ITEM_RETURNED->value => self::ITEM_RETURNED->getLabel(),
            self::REFUND_PROCESSED->value => self::REFUND_PROCESSED->getLabel(),
        ];
    }
}
