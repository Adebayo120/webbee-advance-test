<?php

namespace App\Filters;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;

abstract class FilterAbstract
{
    public abstract function filter(Builder $builder, string|array|int|null $value): Builder;

    protected function mappings(): array
    {
        return [];
    }

    protected function resolveFilterValue($key)
    {
        return Arr::get($this->mappings(), $key);
    }

    protected function resolveOrderDirection(string $direction): string
    {
        return Arr::get([
            'desc'          => 'desc',
            'descending'    => 'desc',
            'asc'           => 'asc',
            'ascending'     => 'asc'
        ], $direction, 'desc');
    }
}
