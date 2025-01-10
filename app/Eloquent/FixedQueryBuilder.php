<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Builder;

/**
 * A query builder which uses the trait {@link FixedQueryBuilderTrait}.
 *
 * This is the "default" query builder for most of our models.
 * This query builder fixes {@link \Illuminate\Database\Eloquent\Builder}
 * such that method used by Lychee throw proper exceptions.
 * See {@link FixedQueryBuilderTrait} for details.
 *
 * Although this class extends `Builder<TModelClass>` and `Builder<TModelClass>`
 * has a `@mixin` for `Illuminate\Database\Query\Builder`, PhpStan does not
 * consider this mixin as part of this class, because this mixin is treated
 * in a special way by the Larastan extension, but Larastan does not know
 * anything about our `FixedQueryBuilder`.
 * For this reason me must repeat all the methods defined by
 * `Illuminate\Database\Query\Builder`.
 * Moreover, many of these methods return `$this`, which is why we cannot use
 * `@mixin` as otherwise the return type does not match.
 * See this [PhpStan Playground](https://phpstan.org/r/f3415be1-fe6b-43fb-8be1-f712cd3e24b1)
 * for an explanation what happens.
 *
 * @template TModelClass of \Illuminate\Database\Eloquent\Model
 *
 * @method $this addSelect(array|mixed $column)
 * @method int   count(string $columns = '*')
 * @method $this from(\Closure|\Illuminate\Database\Query\Builder|string $table, ?string $as = null)
 * @method $this join(string $table, \Closure|string $first, ?string $operator = null, ?string $second = null, string $type = 'inner', bool $where = false)
 * @method $this limit(int $value)
 * @method $this offset(int $value)
 * @method $this select(array|mixed $columns = ['*'])
 * @method $this join(string $table, \Closure|string $first, ?string $operator = null, $second = null, $type = 'inner', $where = false)
 * @method $this joinSub(\Closure|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|string $query, string $as, \Closure|string $first, ?string $operator = null, $second = null, $type = 'inner', $where = false)
 * @method $this leftJoin(string $table, \Closure|string $first, ?string $operator = null, $second = null, $type = 'inner', $where = false)
 * @method $this take(int $value)
 * @method void  truncate()
 * @method $this whereColumn(string|array $first, ?string $operator = null, ?string $second = null, string $boolean = 'and')
 * @method $this whereExists(Closure $callback, string $boolean = 'and', bool $not = false)
 * @method $this whereIn(string $column, mixed $values, string $boolean = 'and', bool $not = false)
 * @method $this whereNotExists(Closure $callback, string $boolean = 'and')
 * @method $this whereNotNull(string|array $columns, string $boolean = 'and')
 * @method $this whereNotIn(string $column, mixed $values, string $boolean = 'and')
 * @method $this whereNull(string|array $columns, string $boolean = 'and', bool $not = false)
 * @method $this orderByDesc($column)
 *
 * @extends Builder<TModelClass>
 */
class FixedQueryBuilder extends Builder
{
	/** @phpstan-use FixedQueryBuilderTrait<TModelClass> */
	use FixedQueryBuilderTrait;
}
