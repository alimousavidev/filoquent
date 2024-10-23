<?php

namespace AliMousavi\Filoquent\Filters;

use AliMousavi\Filoquent\Contracts\FilterInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class FilterAbstract implements FilterInterface
{
    public function __construct(protected Request $request) {

    }
    protected Builder $builder;

    protected array $filterables = [];

    protected array $searchables = [];

    protected string $searchField = self::SEARCH;

    protected array $orderBy = [];

    private array $availableTypes = [
        self::TYPE_INTEGER,
        self::TYPE_STRING,
        self::TYPE_BOOLEAN,
        self::TYPE_FLOAT,
        self::TYPE_DOUBLE,
    ];

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
                $value = in_array(strtolower($value), ['1', 'on', 'true', 'yes']);
            }

            settype($value, $type);

            $this->$filter($value);
        }

        if ($this->request->filled($this->searchField)) {
            $this->search($this->request->input($this->searchField));
        }

        $this->order();
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

    public function order(): void
    {
        foreach ($this->orderBy as $key => $item) {
            if (is_string($key)) {
                $this->builder->orderBy($key, $item);
            } else {
                $this->builder->orderBy($item);
            }
        }
    }

    public function search($phrase): void
    {
        $searchables = $this->searchables;
        $this->builder->where(function (Builder $query) use ($phrase, $searchables) {
            foreach ($searchables as $searchable) {
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
			$nestedQuery->where($property, "%{$phrase}%");
		});
	}
}
