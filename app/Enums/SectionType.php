<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum SectionType: string implements HasColor, HasIcon, HasLabel
{
    case VIRTUAL = 'VIRTUAL';
    case REAL = 'REAL';

    public function getColor(): ?string
    {
        return match ($this) {
            self::VIRTUAL => 'info',
            self::REAL => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::VIRTUAL => 'heroicon-o-cube-transparent',
            self::REAL => 'heroicon-o-cube',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::VIRTUAL => app()->getLocale() == 'ar' ? 'افتراضي' : 'Virtual',
            self::REAL => app()->getLocale() == 'ar' ? 'فعلي' : 'Real',
        };
    }

    public static function toSelectArray(): array
    {
        return [
            self::VIRTUAL->value => self::VIRTUAL->getLabel(),
            self::REAL->value => self::REAL->getLabel(),
        ];
    }
}
