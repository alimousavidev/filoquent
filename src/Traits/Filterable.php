<?php

namespace AliMousavi\Filoquent\Traits;

use AliMousavi\Filoquent\Contracts\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    public function scopeFilter(Builder $builder, FilterInterface $filter)
    {
        $filter->apply($builder);
    }
}
