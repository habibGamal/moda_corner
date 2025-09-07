<?php

namespace App\Enums;

enum ExpirationUnit: string
{
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';
    case YEAR = 'year';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
