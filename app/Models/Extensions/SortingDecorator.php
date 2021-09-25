<?php

namespace App\Models\Extensions;

use App\Exceptions\Internal\InvalidOrderDirectionException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SortingDecorator
{
	const COLUMN_ID = 'id';
	const COLUMN_TAKEN_AT = 'taken_at';
	const COLUMN_TITLE = 'title';
	const COLUMN_DESCRIPTION = 'description';
	const COLUMN_IS_PUBLIC = 'is_public';
	const COLUMN_IS_STARRED = 'is_starred';
	const COLUMN_TYPE = 'type';

	const COLUMNS = [
		self::COLUMN_ID,
		self::COLUMN_TAKEN_AT,
		self::COLUMN_TITLE,
		self::COLUMN_DESCRIPTION,
		self::COLUMN_IS_PUBLIC,
		self::COLUMN_IS_STARRED,
		self::COLUMN_TYPE,
	];

	const POSTPONE_COLUMNS = [
		self::COLUMN_TITLE,
		self::COLUMN_DESCRIPTION,
	];

	protected Builder $baseBuilder;

	public function __construct(Builder $baseBuilder)
	{
		$this->baseBuilder = $baseBuilder;
	}

	/** @var array{column: string, direction:string}[] */
	protected array $postponedSortBy = [];

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
		if (in_array($column, self::POSTPONE_COLUMNS)) {
			if (!in_array($direction, ['asc', 'desc'], true)) {
				throw new InvalidOrderDirectionException();
			}
			$this->postponedSortBy[] = [
				'column' => $column,
				'direction' => $direction,
			];
		} else {
			try {
				$this->baseBuilder = $this->baseBuilder->orderBy($column, $direction);
			} catch (\InvalidArgumentException $e) {
				// Sic! In theory, `\InvalidArgumentException` should be thrown
				// if the *type* of argument differs from the expected type
				// (e.g. a method gets pass an integer, but requires a string).
				// If the *value* is invalid, the method should throw a
				// `\InvalidDomainException`.
				// But Eloquent throws `\InvalidArgumentException` if the
				// direction does neither equal "asc" nor "desc".
				throw new InvalidOrderDirectionException();
			}
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
			$result = $result->sortBy(
				$criterion['column'],
				SORT_NATURAL | SORT_FLAG_CASE,
				$criterion['direction'] === 'desc'
			);
		}

		return $result;
	}
}
