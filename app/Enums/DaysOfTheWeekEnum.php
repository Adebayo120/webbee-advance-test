<?php

namespace App\Enums;

enum DaysOfTheWeekEnum: int
{
    case SUNDAY = 0;
    
    case MONDAY = 1;

    case TUESDAY = 2;

    case WEDNESDAY = 3;

    case THURSDAY = 4;

    case FRIDAY = 5;

    case SATURDAY = 6;

    public static function getAllValues(): array
    {
        return array_column(DaysOfTheWeekEnum::cases(), 'value');
    }
}
