<?php

namespace AliMousavi\Filoquent\Filters;

use AliMousavi\Filoquent\Contracts\FilterInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class FilterAbstract implements FilterInterface
{
    public function __construct(protected Request $request) {}

    protected Builder $builder;

    protected array $filterables = [];

    protected array $searchables = [];

    protected array $orderables = [];

    protected string $searchField = self::SEARCH;

    protected string $sortField = self::ORDER_BY;

    protected array $orderBy = [];

    private array $availableTypes = [
        self::TYPE_INTEGER,
        self::TYPE_STRING,
        self::TYPE_BOOLEAN,
        self::TYPE_FLOAT,
        self::TYPE_DOUBLE,
    ];

    /**
     * Apply filters, search, and ordering to the Eloquent builder.
     *
     * @param Builder $builder
     */


    public function apply(Builder $builder): void
    {
        $this->builder = $builder;
        $filters = $this->getFilters();

        foreach ($filters as $filter => $value) {
            if (! method_exists($this, $filter)) {
                continue;
            }

            $type = $this->filterables[$filter];

            if (! in_array($type, $this->availableTypes)) {
                continue;
            }

            if ($type == self::TYPE_BOOLEAN) {
                $value = $this->isTruthy($value);
            }

            settype($value, $type);

            $this->$filter($value);
        }

        if ($this->request->filled($this->searchField)) {
            $this->search($this->request->input($this->searchField));
        }

        $this->applyOrdering();
    }

    public function getFilters(): array
    {
        return array_filter($this->request->only($this->getFilterables()), function ($filterable) {
            return ! is_null($filterable);
        });
    }

    public function getFilterables(): array
    {
        return array_keys($this->filterables);
    }

    public function applyOrdering(): void
    {
        $orderBy = [];

        if ($this->request->filled($this->sortField)) {
            $orderBy = $this->parseOrderParameters($this->request->query($this->sortField));
        }

        if (empty($orderBy)) {
            $orderBy = $this->orderBy;
        }

        foreach ($orderBy as $column => $direction) {
            if (is_int($column)) {
                $column = $direction;
                $direction = 'asc';
            }

            if (isset($this->orderables[$column])) {
                $method = $this->orderables[$column];

                if (is_callable([$this, $method])) {
                    $this->$method($direction);

                    continue;
                }
            }

            $this->builder->orderBy($column, $direction);
        }
    }
    private function isTruthy(mixed $value): bool
    {
        return in_array(strtolower((string) $value), ['1', 'on', 'true', 'yes'], true);
    }


    private function parseOrderParameters(string $input): array
    {
        $segments = explode(',', $input);
        $orderBy = [];

        foreach ($segments as $parameter) {
            [$field, $direction] = str_contains($parameter, ':')
            ? explode(':', $parameter)
            : [$parameter, 'asc'];

            if (empty($field) || ! $this->isSortable($field)) {
                continue;
            }

            $orderBy[$field] = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';
        }

        return $orderBy;
    }

    private function isSortable(string $field)
    {
        $orderableFields = [];
        foreach ($this->orderables as $key => $item) {
            if (is_string($key)) {
                $orderableFields[] = $key;
            } else {
                $orderableFields[] = $item;
            }
        }

        return in_array($field, $orderableFields);
    }

    public function search($phrase): void
    {
        $this->builder->where(function (Builder $query) use ($phrase) {
            foreach ($this->searchables as $searchable) {
                if (str_contains($searchable, '.')) {
                    $this->nestedSearch($query, $searchable, $phrase);
                } else {
                    $query->orWhere($searchable, 'like', "%{$phrase}%");
                }
            }
        });
    }

    private function nestedSearch(Builder $query, string $searchable, string $phrase)
    {
        $parts = explode('.', $searchable);
        $property = array_pop($parts);
        $relation = implode('.', $parts);

        $query->orWhereHas($relation, function (Builder $nestedQuery) use ($property, $phrase) {
            $nestedQuery->where($property, "like","%{$phrase}%");
        });
    }
}
