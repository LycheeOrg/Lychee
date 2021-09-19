<?php

namespace App\Models\Extensions;

use App\Exceptions\Internal\InvalidOrderDirectionException;
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

	/**
	 * @throws InvalidOrderDirectionException
	 */
	public function orderBy($column, $direction = 'asc'): SortingDecorator
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
