<?php

namespace App\Models\Extensions;

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
		return new FixedQueryBuilder($query); // @phpstan-ignore-line
	}
}
