<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class ModelFilters
{
    protected array $filters = [];

    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function filter(Builder $builder): Builder
    {
        $filterableRequestKeys = array_keys($this->filters);
        foreach ($this->data as $key => $value) {
            if (!in_array( $key, $filterableRequestKeys)) {
                continue;
            }
            $this->resolveFilter($key)->filter($builder, $value);
        }

        return $builder;
    }

    /**
     * Instantiate a filter.
     *
     * @param  string $filter
     * @return mixed
     */
    protected function resolveFilter($filter)
    {
        return new $this->filters[$filter];
    }
}
