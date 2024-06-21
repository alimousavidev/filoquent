<?php

namespace AliMousavi\Filoquent\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface FilterableInterface
{
    public function scopeFilter(Builder $builder, FilterInterface $filter);
}
