<?php

namespace App\Models\Extensions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SortingDecorator
{
	public const POSTPONE_COLUMNS = [
		'title',
		'description',
	];

	protected Builder $baseBuilder;

	public function __construct(Builder $baseBuilder)
	{
		$this->baseBuilder = $baseBuilder;
	}

	/**
	 * The list of all sorting criteria in descending priority.
	 *
	 * The sorting criterion at index 0 is the most significant criterion;
	 * the sorting criterion at index `length-1` is the least significant
	 * criterion.
	 *
	 * If everything can be sorted on the SQL layer, then the SQL basically
	 * has to look like that:
	 *
	 *     $query->orderBy($orderBy[0])->orderBy($orderBy[1])->...->orderBy($orderBy[length-1])
	 *
	 * For SQL the most significant order criterion has to be put first.
	 *
	 * If everything needs to be sorted on the software layer (i.e. with
	 * Laravel Collections), then the criteria must be applied in reverse
	 * order like this
	 *
	 *     $collection->sortBy($orderBy[length-1])->...->sortBy($orderBy[1])->sortBy($orderBy[0])
	 *
	 * The reason is that each `sortBy` immediately executes a _stable_ sort
	 * and thus the last one "wins".
	 *
	 * The mixed case with some pre-sorting on the SQL layer and final sorting
	 * on the software layer is more complicated.
	 *
	 * @var array{column: string, direction:string}[]
	 */
	protected array $orderBy = [];

	/**
	 * The index for {@link SortingDecorator::$orderBy} at which we must
	 * switch from SQL sorting to PHP sorting.
	 *
	 * Criteria between `0` ... `$pivotIdx` are sorted on the software layer
	 * (in reverse order).
	 * Criteria between `$pivotIdx+1` ... `length-1` are sorted on the SQL
	 * layer.
	 *
	 * If `$pivotIdx == -1`, then everything is sorted on the SQL layer.
	 * `$pivotIdx` is only set to a different value, if a sorting criteria
	 * which must be postponed (see {@link SortingDecorator::POSTPONE_COLUMNS})
	 * is added.
	 * Then `$pivotIdx` points to that with the least priority, because from
	 * there on everything must be sorted in software.
	 *
	 * @var int
	 */
	protected int $pivotIdx = -1;

	public function orderBy($column, $direction = 'asc'): SortingDecorator
	{
		$direction = strtolower($direction);
		if (!in_array($direction, ['asc', 'desc'], true)) {
			throw new \InvalidArgumentException('Order direction must be "asc" or "desc".');
		}
		$this->orderBy[] = [
			'column' => $column,
			'direction' => $direction,
		];

		if (in_array($column, self::POSTPONE_COLUMNS)) {
			$this->pivotIdx = sizeof($this->orderBy) - 1;
		}

		return $this;
	}

	public function get($columns = ['*']): Collection
	{
		// Sort as much as we can on the SQL layer, i.e. everything with a
		// lower significance than the least significant criterion which
		// requires natural sorting.
		for ($i = $this->pivotIdx + 1; $i < sizeof($this->orderBy); $i++) {
			$this->baseBuilder->orderBy($this->orderBy[$i]['column'], $this->orderBy[$i]['direction']);
		}

		/** @var Collection $result */
		$result = $this->baseBuilder->get($columns);

		// Sort with PHP for the remaining criteria in reverse order.
		for ($i = $this->pivotIdx; $i >= 0; $i--) {
			$column = $this->orderBy[$i]['column'];
			$options = in_array($column, self::POSTPONE_COLUMNS) ? SORT_NATURAL | SORT_FLAG_CASE : SORT_REGULAR;
			$result = $result->sortBy(
				$column,
				$options,
				$this->orderBy[$i]['direction'] === 'desc'
			)->values();
		}

		return $result;
	}
}
