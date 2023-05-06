<?php

namespace App\Filters\Slot;

use App\Filters\ModelFilters;
use App\Filters\DateRangeFilter;

class SlotFilters extends ModelFilters
{
    protected array $filters = [
        'date_range' => DateRangeFilter::class
    ];
}