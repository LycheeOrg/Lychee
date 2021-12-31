<?php

namespace App\Models\Extensions;

use App\Exceptions\Internal\InvalidOrderDirectionException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SortingDecorator
{
	public const COLUMN_ID = 'id';
	public const COLUMN_TAKEN_AT = 'taken_at';
	public const COLUMN_TITLE = 'title';
	public const COLUMN_DESCRIPTION = 'description';
	public const COLUMN_IS_PUBLIC = 'is_public';
	public const COLUMN_IS_STARRED = 'is_starred';
	public const COLUMN_TYPE = 'type';

	public const COLUMNS = [
		self::COLUMN_ID,
		self::COLUMN_TAKEN_AT,
		self::COLUMN_TITLE,
		self::COLUMN_DESCRIPTION,
		self::COLUMN_IS_PUBLIC,
		self::COLUMN_IS_STARRED,
		self::COLUMN_TYPE,
	];

	public const POSTPONE_COLUMNS = [
		self::COLUMN_TITLE,
		self::COLUMN_DESCRIPTION,
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

	/**
	 * @param string $column    the column acc. to which the result shall be
	 *                          sorted; must either be
	 *                          {@link SortingDecorator::COLUMN_ID},
	 *                          {@link SortingDecorator::COLUMN_TAKEN_AT},
	 *                          {@link SortingDecorator::COLUMN_TITLE},
	 *                          {@link SortingDecorator::COLUMN_DESCRIPTION},
	 *                          {@link SortingDecorator::COLUMN_IS_PUBLIC},
	 *                          {@link SortingDecorator::COLUMN_IS_STARRED}, or
	 *                          {@link SortingDecorator::COLUMN_TYPE},
	 * @param string $direction the order direction must be either `'asc'` or
	 *                          `'desc'`
	 *
	 * @throws InvalidOrderDirectionException
	 */
	public function orderBy(string $column, string $direction = 'asc'): SortingDecorator
	{
		$direction = strtolower($direction);
		if (!in_array($direction, ['asc', 'desc'], true)) {
			throw new InvalidOrderDirectionException();
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

	/**
	 * Gets the result collection.
	 *
	 * @param string[] $columns
	 *
	 * @return Collection
	 *
	 * @throws InvalidOrderDirectionException
	 */
	public function get(array $columns = ['*']): Collection
	{
		// Sort as much as we can on the SQL layer, i.e. everything with a
		// lower significance than the least significant criterion which
		// requires natural sorting.
		try {
			for ($i = $this->pivotIdx + 1; $i < sizeof($this->orderBy); $i++) {
				$this->baseBuilder->orderBy($this->orderBy[$i]['column'], $this->orderBy[$i]['direction']);
			}
		} catch (\InvalidArgumentException) {
			// Sic! In theory, `\InvalidArgumentException` should be thrown
			// if the *type* of argument differs from the expected type
			// (e.g. a method gets pass an integer, but requires a string).
			// If the *value* is invalid, the method should throw a
			// `\InvalidDomainException`.
			// But Eloquent throws `\InvalidArgumentException` if the
			// direction does neither equal "asc" nor "desc".
			throw new InvalidOrderDirectionException();
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
