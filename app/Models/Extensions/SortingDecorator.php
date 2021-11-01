<?php

namespace App\Models\Extensions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SortingDecorator
{
	const POSTPONE_COLUMNS = [
		'title',
		'description',
	];

	protected Builder $baseBuilder;

	public function __construct(Builder $baseBuilder)
	{
		$this->baseBuilder = $baseBuilder;
	}

	/** @var array{column: string, direction:string}[] */
	protected array $postponedSortBy = [];

	public function orderBy($column, $direction = 'asc'): SortingDecorator
	{
		$direction = strtolower($direction);
		if (in_array($column, self::POSTPONE_COLUMNS)) {
			if (!in_array($direction, ['asc', 'desc'], true)) {
				throw new \InvalidArgumentException('Order direction must be "asc" or "desc".');
			}
			$this->postponedSortBy[] = [
				'column' => $column,
				'direction' => $direction,
			];
		} else {
			$this->baseBuilder = $this->baseBuilder->orderBy($column, $direction);
		}

		return $this;
	}

	public function get($columns = ['*']): Collection
	{
		/** @var Collection $result */
		$result = $this->baseBuilder->get($columns);

		for (
			end($this->postponedSortBy);
			key($this->postponedSortBy) !== null && $criterion = current($this->postponedSortBy);
			prev($this->postponedSortBy)
		) {
			$sortedResult = $result->sortBy(
				$criterion['column'],
				SORT_NATURAL | SORT_FLAG_CASE,
				$criterion['direction'] === 'desc'
			)->values();
		}

		return $sortedResult;
	}
}
