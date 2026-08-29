<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models\Extensions;

use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Exceptions\Internal\InvalidOrderDirectionException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @template TModelClass of \Illuminate\Database\Eloquent\Model
 */
class SortingDecorator
{
	/**
	 * @var Builder<TModelClass>
	 */
	protected Builder|Relation $base_builder;

	/**
	 * @param Builder<TModelClass> $base_builder
	 *
	 * @return void
	 */
	public function __construct(Builder|Relation $base_builder)
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
	 * @var array<int,array{column:string,direction:string,type:ColumnSortingType,prefix:string}>
	 */
	protected array $order_by = [];

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
			'type' => $column,
			'prefix' => '',
		];

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
			'type' => $column,
			'prefix' => 'photos.',
		];

		return $this;
	}

	/**
	 * Apply SQL-level sorting for all criteria to the underlying builder,
	 * without executing the query.
	 *
	 * Used by eager-loading relation classes (e.g. {@link \App\Relations\HasManyPhotosByTag})
	 * which must order their own query builder directly, since Eloquent's
	 * default eager-load path bypasses {@link SortingDecorator::get()}
	 * (FR-060-08).
	 *
	 * @return SortingDecorator<TModelClass>
	 *
	 * @throws InvalidOrderDirectionException
	 */
	public function applyOrdering(): self
	{
		try {
			foreach ($this->order_by as $criterion) {
				$column_type = $criterion['type'];
				$prefix = $criterion['prefix'];
				$direction = OrderSortingType::from($criterion['direction']);

				// Check if this column requires raw SQL ordering (e.g., COALESCE for rating, or title_base/title_index for title)
				if ($column_type->requiresRawOrdering()) {
					$this->base_builder->orderByRaw($column_type->getRawOrderExpression($prefix, $direction));
				} else {
					$this->base_builder->orderBy($criterion['column'], $criterion['direction']);
				}
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
		$this->applyOrdering();

		return $this->base_builder->get($columns);
	}

	/**
	 * Paginate the given query.
	 *
	 * @param int|null                                 $per_page
	 * @param array<int, (model-property<TModel>|'*')> $columns
	 * @param string                                   $page_name
	 * @param int|null                                 $page
	 *
	 * @return \Illuminate\Pagination\LengthAwarePaginator<int,TModelClass>
	 *
	 * @throws \InvalidArgumentException
	 */
	public function paginate($per_page = null, $columns = ['*'], $page_name = 'page', $page = null)
	{
		$this->applyOrdering();

		return $this->base_builder->paginate($per_page, $columns, $page_name, $page);
	}
}
