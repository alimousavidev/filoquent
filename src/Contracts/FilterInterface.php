<?php

namespace AliMousavi\Filoquent\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface FilterInterface
{
    const TYPE_BOOLEAN = 'boolean';

    const TYPE_INTEGER = 'integer';

    const TYPE_STRING = 'string';
    const TYPE_FLOAT = 'float';
    const TYPE_DOUBLE = 'double';

    const SEARCH = 'search';

    public function apply(Builder $builder): void;

    public function getFilters(): array;

    public function getFilterables(): array;

    public function order();

    public function search($phrase): void;
}
