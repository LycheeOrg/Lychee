<?php

namespace App\Eloquent;

/**
 * Models which use this trait use {@link FixedQueryBuilder} instead of the standard {@link \Illuminate\Database\Eloquent\Builder}.
 *
 * Models which use {@link FixedQueryBuilder} as their query builder allow
 * making use of proper exception handling.
 * See {@link FixedQueryBuilderTrait} for details.
 *
 * @template TModelClass of \Illuminate\Database\Eloquent\Model
 *
 * @method static FixedQueryBuilder<TModelClass> query()                       Begin querying the model.
 * @method static FixedQueryBuilder<TModelClass> with(array|string $relations) Begin querying the model with eager loading.
 */
trait UseFixedQueryBuilder
{
	/**
	 * @param $query
	 *
	 * @return FixedQueryBuilder<TModelClass>
	 */
	public function newEloquentBuilder($query): FixedQueryBuilder
	{
		// We must return `FixedQueryBuilder<TModelClass>` but the
		// `new`-statement evaluates to `FixedQueryBuilder` (without a bound
		// template parameter).
		// @phpstan-ignore-next-line
		return new FixedQueryBuilder($query);
	}
}
