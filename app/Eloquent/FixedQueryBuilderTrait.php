<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Eloquent;

use App\Exceptions\Internal\QueryBuilderException;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as BaseBuilder;

/**
 * Fixed Eloquent query builder.
 *
 * This trait decorates some Eloquent builder methods to make error handling
 * more consistent.
 *
 * Some Eloquent builder methods throw spurious undocumented or documented
 * but impracticable exceptions.
 * "Impracticable" means that the exceptions are not specific to Eloquent,
 * although they originate from inside Eloquent (and not from another even
 * more basic function called by Eloquent).
 * This makes it difficult to specifically catch these exceptions on a higher
 * level of the call stack.
 * This leaves us with two options:
 * Either wrap each and every call to Eloquent into an exception handler
 * in-place like this
 *
 *     try {
 *       $models = MyModel::query()->where(...)->orderBy(...)->get();
 *     } catch (\Throwable $e) {
 *       throw new QueryBuilderException($e);
 *     }
 *
 * or use a decorator like this trait.
 * In order to keep our actual "business logic" clean from work-arounds for
 * awkward design decisions of Eloquent, we use this decorator.
 * Hopefully, the necessity for this trait will vanish in the future after
 * Eloquent has adopted to proper error handling.
 * See [Laravel Discussion #40020](https://github.com/laravel/framework/discussions/40020).
 *
 * _Note:_ This trait does not wrap every method of the underlying
 * {@link \Illuminate\Database\Eloquent\Builder}; only those which are used
 * by Lychee.
 *
 * @template TModelClass of \Illuminate\Database\Eloquent\Model
 */
trait FixedQueryBuilderTrait
{
	/**
	 * Add a basic where clause to the query.
	 *
	 * @param \Closure|string|array<int|string,mixed>|Expression $column
	 * @param mixed                                              $operator
	 * @param mixed                                              $value
	 * @param string                                             $boolean
	 *
	 * @return $this
	 *
	 * @throws QueryBuilderException
	 */
	public function where($column, $operator = null, $value = null, $boolean = 'and'): static
	{
		try {
			// @phpstan-ignore-next-line; due to the Larastan rules set PhpStan falsely assumes we are calling a static method
			return parent::where($column, $operator, $value, $boolean);
			// @codeCoverageIgnoreStart
		} catch (\Throwable $e) {
			throw new QueryBuilderException($e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Add a "where in" clause to the query.
	 *
	 * @param string $column
	 * @param mixed  $values
	 * @param string $boolean
	 * @param bool   $not
	 *
	 * @return $this
	 *
	 * @throws QueryBuilderException
	 */
	public function whereIn($column, $values, $boolean = 'and', $not = false): static
	{
		try {
			// @phpstan-ignore-next-line; due to the Larastan rules set PhpStan falsely assumes we are calling a static method
			return parent::whereIn($column, $values, $boolean, $not);
			// @codeCoverageIgnoreStart
		} catch (\Throwable $e) {
			throw new QueryBuilderException($e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Add a "where not in" clause to the query.
	 *
	 * @param string $column
	 * @param mixed  $values
	 * @param string $boolean
	 *
	 * @return $this
	 *
	 * @throws QueryBuilderException
	 */
	public function whereNotIn($column, $values, $boolean = 'and'): static
	{
		try {
			// @phpstan-ignore-next-line; due to the Larastan rules set PhpStan falsely assumes we are calling a static method
			return parent::whereNotIn($column, $values, $boolean);
			// @codeCoverageIgnoreStart
		} catch (\Throwable $e) {
			throw new QueryBuilderException($e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Set the columns to be selected.
	 *
	 * @param array|mixed $columns
	 *
	 * @return $this
	 *
	 * @throws QueryBuilderException
	 */
	public function select($columns = ['*']): static
	{
		try {
			// @phpstan-ignore-next-line; due to the Larastan rules set PhpStan falsely assumes we are calling a static method
			return parent::select($columns);
			// @codeCoverageIgnoreStart
		} catch (\Throwable $e) {
			throw new QueryBuilderException($e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Add a join clause to the query.
	 *
	 * @param string          $table
	 * @param \Closure|string $first
	 * @param string|null     $operator
	 * @param string|null     $second
	 * @param string          $type
	 * @param bool            $where
	 *
	 * @return $this
	 *
	 * @throws QueryBuilderException
	 */
	public function join($table, $first, $operator = null, $second = null, $type = 'inner', $where = false): static
	{
		try {
			// @phpstan-ignore-next-line; due to the Larastan rules set PhpStan falsely assumes we are calling a static method
			return parent::join($table, $first, $operator, $second, $type, $where);
			// @codeCoverageIgnoreStart
		} catch (\Throwable $e) {
			throw new QueryBuilderException($e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Add a subquery join clause to the query.
	 *
	 * @param \Closure|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder<TModelClass>|string $query
	 * @param string                                                                                                $as
	 * @param \Closure|string                                                                                       $first
	 * @param string|null                                                                                           $operator
	 * @param string|null                                                                                           $second
	 * @param string                                                                                                $type
	 * @param bool                                                                                                  $where
	 *
	 * @return $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function joinSub($query, $as, $first, $operator = null, $second = null, $type = 'inner', $where = false): static
	{
		try {
			// @phpstan-ignore-next-line; due to the Larastan rules set PhpStan falsely assumes we are calling a static method
			return parent::joinSub($query, $as, $first, $operator, $second, $type, $where);
			// @codeCoverageIgnoreStart
		} catch (\Throwable $e) {
			throw new QueryBuilderException($e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Add a left join to the query.
	 *
	 * @param string          $table
	 * @param \Closure|string $first
	 * @param string|null     $operator
	 * @param string|null     $second
	 *
	 * @return $this
	 *
	 * @throws QueryBuilderException
	 */
	public function leftJoin($table, $first, $operator = null, $second = null): static
	{
		try {
			// @phpstan-ignore-next-line; due to the Larastan rules set PhpStan falsely assumes we are calling a static method
			return parent::leftJoin($table, $first, $operator, $second);
			// @codeCoverageIgnoreStart
		} catch (\Throwable $e) {
			throw new QueryBuilderException($e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Add an "order by" clause to the query.
	 *
	 * @param \Closure|Builder<TModelClass>|BaseBuilder|Expression|string $column
	 * @param string                                                      $direction
	 *
	 * @return $this
	 *
	 * @throws QueryBuilderException
	 */
	public function orderBy($column, $direction = 'asc'): static
	{
		try {
			// The parent class is Eloquent\Builder and Eloquent\Builder::orderBy()
			// accepts exactly the types for columns as listed above
			// (see source code of the framework).
			// However, the buggy larastan ruleset lies to PhpStan about the
			// types and hence we must ignore this line.
			//
			// @phpstan-ignore-next-line
			return parent::orderBy($column, $direction);
			// @codeCoverageIgnoreStart
		} catch (\Throwable $e) {
			throw new QueryBuilderException($e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Add a new select column to the query.
	 *
	 * @param array|mixed $column
	 *
	 * @return $this
	 *
	 * @throws QueryBuilderException
	 */
	public function addSelect($column): static
	{
		try {
			// @phpstan-ignore-next-line; due to the Larastan rules set PhpStan falsely assumes we are calling a static method
			return parent::addSelect($column);
			// @codeCoverageIgnoreStart
		} catch (\Throwable $e) {
			throw new QueryBuilderException($e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Add an "or where" clause to the query.
	 *
	 * @param \Closure|string|array<int|string,mixed>|Expression $column
	 * @param mixed                                              $operator
	 * @param mixed                                              $value
	 *
	 * @return $this
	 *
	 * @throws QueryBuilderException
	 */
	public function orWhere($column, $operator = null, $value = null): static
	{
		try {
			// @phpstan-ignore-next-line; due to the Larastan rules set PhpStan falsely assumes we are calling a static method
			return parent::orWhere($column, $operator, $value);
			// @codeCoverageIgnoreStart
		} catch (\Throwable $e) {
			throw new QueryBuilderException($e);
		}
		// @codeCoverageIgnoreEnd
	}
}
