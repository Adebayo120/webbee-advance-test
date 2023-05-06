<?php

namespace App\Filters;

use Carbon\Carbon;
use App\Filters\FilterAbstract;
use Illuminate\Database\Eloquent\Builder;

class DateRangeFilter extends FilterAbstract
{
    /**
     * Apply filter.
     *
     * @param Builder $builder
     * @param string|array|int|null $value
     *
     * @return Builder
     */
    public function filter(Builder $builder, string|array|int|null $value): Builder
    {
        if (
            is_null($value) ||
            !is_array($value) ||
            !isset($value['start_date_in_unix_timestamp']) ||
            !isset($value['end_date_in_unix_timestamp'])
        ) {
            return $builder;
        }
        
        $start = ($value['start_date_in_unix_timestamp'] instanceof Carbon) ? 
                    $value['start_date_in_unix_timestamp'] :
                    Carbon::createFromTimestamp($value['start_date_in_unix_timestamp']);

        $end = ($value['end_date_in_unix_timestamp'] instanceof Carbon) ? 
                    $value['end_date_in_unix_timestamp'] :
                    Carbon::createFromTimestamp($value['end_date_in_unix_timestamp']);

        return $builder->whereBetween('start_date', [$start->startOfDay(), $end->endOfDay()]);
    }
}