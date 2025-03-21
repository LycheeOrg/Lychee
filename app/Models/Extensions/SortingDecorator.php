<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models\Extensions;

use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Exceptions\Internal\InvalidOrderDirectionException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @template TModelClass of \Illuminate\Database\Eloquent\Model
 */
class SortingDecorator
{
	public const POSTPONE_COLUMNS = [
		ColumnSortingType::TITLE,
		ColumnSortingType::DESCRIPTION,
	];

	/**
	 * @var Builder<TModelClass>
	 */
	protected Builder $base_builder;

	/**
	 * @param Builder<TModelClass> $base_builder
	 *
	 * @return void
	 */
	public function __construct(Builder $base_builder)
	{
		$this->base_builder = $base_builder;
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
	 *     $query->order_by($orderBy[0])->order_by($orderBy[1])->...->order_by($orderBy[length-1])
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
	 * @var array<int,array{column:string,direction:string}>
	 */
	protected array $order_by = [];

	/**
	 * The index for {@link SortingDecorator::$orderBy} at which we must
	 * switch from SQL sorting to PHP sorting.
	 *
	 * Criteria between `0` ... `$pivotIdx` are sorted on the software layer
	 * (in reverse order).
	 * Criteria between `$pivotIdx+1` ... `length-1` are sorted on the SQL
	 * layer.
	 *
	 * If `$pivotIdx === -1`, then everything is sorted on the SQL layer.
	 * `$pivotIdx` is only set to a different value, if a sorting criteria
	 * which must be postponed (see {@link SortingDecorator::POSTPONE_COLUMNS})
	 * is added.
	 * Then `$pivotIdx` points to that with the least priority, because from
	 * there on everything must be sorted in software.
	 *
	 * @var int
	 */
	protected int $pivot_idx = -1;

	/**
	 * @param ColumnSortingType $column    the column acc. to which the result shall be sorted
	 * @param OrderSortingType  $direction the order direction
	 *
	 * @return SortingDecorator<TModelClass>
	 *
	 * @throws InvalidOrderDirectionException
	 */
	public function orderBy(ColumnSortingType $column, OrderSortingType $direction): SortingDecorator
	{
		$this->order_by[] = [
			'column' => $column->value,
			'direction' => $direction->value,
		];

		if (in_array($column, self::POSTPONE_COLUMNS, true)) {
			$this->pivot_idx = count($this->order_by) - 1;
		}

		return $this;
	}

	/**
	 * Some sorting are done at the photo level, however because we enforce more strictly the type on column
	 * we are now prefixing the column by `photos.`.
	 *
	 * @param ColumnSortingType $column    the column acc. to which the result shall be sorted
	 * @param OrderSortingType  $direction the order direction
	 *
	 * @return SortingDecorator<TModelClass>
	 *
	 * @throws InvalidOrderDirectionException
	 */
	public function orderPhotosBy(ColumnSortingType $column, OrderSortingType $direction): SortingDecorator
	{
		$this->order_by[] = [
			'column' => 'photos.' . $column->value,
			'direction' => $direction->value,
		];

		if (in_array($column, self::POSTPONE_COLUMNS, true)) {
			$this->pivot_idx = count($this->order_by) - 1;
		}

		return $this;
	}

	/**
	 * Gets the result collection.
	 *
	 * @param string[] $columns
	 *
	 * @return Collection<int,TModelClass>
	 *
	 * @throws InvalidOrderDirectionException
	 */
	public function get(array $columns = ['*']): Collection
	{
		// Sort as much as we can on the SQL layer, i.e. everything with a
		// lower significance than the least significant criterion which
		// requires natural sorting.
		try {
			for ($i = $this->pivot_idx + 1; $i < count($this->order_by); $i++) {
				$this->base_builder->order_by($this->order_by[$i]['column'], $this->order_by[$i]['direction']);
			}
			// @codeCoverageIgnoreStart
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
		// @codeCoverageIgnoreEnd

		/** @var Collection<int,TModelClass> $result */
		$result = $this->base_builder->get($columns);

		// Sort with PHP for the remaining criteria in reverse order.
		for ($i = $this->pivot_idx; $i >= 0; $i--) {
			$column = $this->order_by[$i]['column'];

			// This conversion is necessary
			$column_sorting_name = str_replace('photos.', '', $column);
			$column_sorting_type = ColumnSortingType::tryFrom($column_sorting_name) ?? ColumnSortingType::CREATED_AT;

			$options = in_array($column_sorting_type, self::POSTPONE_COLUMNS, true) ? SORT_NATURAL | SORT_FLAG_CASE : SORT_REGULAR;
			$result = $result->sortBy(
				$column_sorting_name,
				$options,
				$this->order_by[$i]['direction'] === OrderSortingType::DESC->value
			)->values();
		}

		return $result;
	}
}
