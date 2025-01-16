<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models\Builders;

use App\Constants\AccessPermissionConstants as APC;
use App\Contracts\Exceptions\InternalLycheeException;
use App\Eloquent\FixedQueryBuilderTrait;
use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Album;
use App\Policies\AlbumQueryPolicy;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Kalnoy\Nestedset\QueryBuilder as NSQueryBuilder;

/**
 * Specialized query builder for {@link \App\Models\Album}.
 *
 * This query builder adds the "virtual" columns `max_taken_at`,
 * `min_taken_at`, `num_children`, `num_photos`, and `is_shared_with_current_user`
 * if actual models are hydrated from the DB.
 * Using a custom query builder rather than a global scope enables more
 * fine-grained control, when the columns are added.
 * A global scope is always added to the query, even if the query is only
 * used as a sub-query which will not hydrate actual models.
 * Thus, a global scope unnecessarily complicates queries in many cases.
 *
 * @method static AlbumBuilder|Album query()                                                                                                                                                                                                                 Begin querying the model.
 * @method        AlbumBuilder|Album with(array|string $relations)                                                                                                                                                                                           Begin querying the model with eager loading.
 * @method        $this              addSelect(array|mixed $column)
 * @method        int                count(string $columns = '*')
 * @method        $this              from(\Closure|\Illuminate\Database\Query\Builder|string $table, ?string $as = null)
 * @method        $this              join(string $table, \Closure|string $first, ?string $operator = null, ?string $second = null, string $type = 'inner', bool $where = false)
 * @method        $this              limit(int $value)
 * @method        $this              offset(int $value)
 * @method        $this              select(array|mixed $columns = ['*'])
 * @method        $this              join(string $table, \Closure|string $first, ?string $operator = null, $second = null, $type = 'inner', $where = false)
 * @method        $this              joinSub(\Closure|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|string $query, string $as, \Closure|string $first, ?string $operator = null, $second = null, $type = 'inner', $where = false)
 * @method        $this              leftJoin(string $table, \Closure|string $first, ?string $operator = null, $second = null, $type = 'inner', $where = false)
 * @method        $this              take(int $value)
 * @method        void               truncate()
 * @method        $this              whereColumn(string|array $first, ?string $operator = null, ?string $second = null, string $boolean = 'and')
 * @method        $this              whereExists(\Closure $callback, string $boolean = 'and', bool $not = false)
 * @method        $this              whereIn(string $column, mixed $values, string $boolean = 'and', bool $not = false)
 * @method        $this              whereNotExists(\Closure $callback, string $boolean = 'and')
 * @method        $this              whereNotNull(string|array $columns, string $boolean = 'and')
 * @method        $this              whereNotIn(string $column, mixed $values, string $boolean = 'and')
 * @method        $this              whereNull(string|array $columns, string $boolean = 'and', bool $not = false)
 * @method        $this              orderByDesc($column)
 *
 * @extends NSQueryBuilder<Album>
 */
class AlbumBuilder extends NSQueryBuilder
{
	/** @phpstan-use FixedQueryBuilderTrait<Album> */
	use FixedQueryBuilderTrait;

	/**
	 * Get the hydrated models without eager loading.
	 *
	 * Adds the "virtual" columns min_taken_at, max_taken_at as well as
	 * num_children and num_photos to the query, if a "full" model is
	 * requested, i.e. if the selected columns are `*` or not given at all.
	 *
	 * @param string[]|string $columns
	 *
	 * @return array<int,Album>
	 *
	 * @throws InternalLycheeException
	 */
	public function getModels($columns = ['*']): array
	{
		$albumQueryPolicy = resolve(AlbumQueryPolicy::class);
		$baseQuery = $this->getQuery();

		if (
			($columns === ['*'] || $columns === ['albums.*']) &&
			($baseQuery->columns === ['*'] || $baseQuery->columns === ['albums.*'] || $baseQuery->columns === null)
		) {
			$countChildren = DB::table('albums', 'a')
				->selectRaw('COUNT(*)')
				->whereColumn('a.parent_id', '=', 'albums.id');

			$countPhotos = DB::table('photos', 'p')
				->selectRaw('COUNT(*)')
				->whereColumn('p.album_id', '=', 'albums.id');

			$this->addSelect([
				'min_taken_at' => $this->getTakenAtSQL()->selectRaw('MIN(taken_at)'),
				'max_taken_at' => $this->getTakenAtSQL()->selectRaw('MAX(taken_at)'),
				'num_children' => $this->applyVisibilityConditioOnSubalbums($countChildren, $albumQueryPolicy),
				'num_photos' => $this->applyVisibilityConditioOnPhotos($countPhotos, $albumQueryPolicy),
			]);
		}

		// The parent method returns a `Model[]`, but we must return
		// `Album[]` and we know that this is indeed the case as we have
		// queried for albums
		return parent::getModels($columns);
	}

	/**
	 * Get statistics of errors of the tree.
	 *
	 * @return array{oddness:int,duplicates:int,wrong_parent:int,missing_parent:int}
	 *
	 * @throws QueryBuilderException
	 */
	public function countErrors(): array
	{
		try {
			return parent::countErrors();
		} catch (\Throwable $e) {
			throw new QueryBuilderException($e);
		}
	}

	/**
	 * Generate a query which tie the taken_at attribute from photos to the albums.
	 * This makes use of nested set, which means that ALL the sub albums are considered.
	 * Do note that no visibility filters are applied.
	 *
	 * @return Builder
	 *
	 * @throws \InvalidArgumentException
	 */
	private function getTakenAtSQL(): Builder
	{
		// Note:
		//  1. The order of JOINS is important.
		//     Although `JOIN` is cumulative, i.e.
		//     `photos JOIN albums` and `albums JOIN photos`
		//     should be identical, it is not with respect to the
		//     MySQL query optimizer.
		//     For an efficient query it is paramount, that the
		//     query first filters out all child albums and then
		//     selects the most/least recent photo within the child
		//     albums.
		//     If the JOIN starts with photos, MySQL first selects
		//     all photos of the entire gallery.
		//  2. The query must use the aggregation functions
		//     `MIN`/`MAX`, we must not use `ORDER BY ... LIMIT 1`.
		//     Otherwise, the MySQL optimizer first selects the
		//     photos and then joins with albums (i.e. the same
		//     effect as above).
		//     The background is rather difficult to explain, but is
		//     due to MySQL's "Limit Query Optimization"
		//     (https://dev.mysql.com/doc/refman/8.0/en/limit-optimization.html).
		//     Basically, if MySQL sees an `ORDER BY ... LIMIT ...`
		//     construction and has an applicable index for that,
		//     MySQL's built-in heuristic chooses that index with high
		//     priority and does not consider any alternatives.
		//     In this specific case, this heuristic fails splendidly.
		//
		// Further note, that PostgreSQL's optimizer is not affected
		// by any of these tricks.
		// The optimized query plan for PostgreSQL is always the same.
		// Good PosgreSQL :-)
		//
		// We must not use `Album::query->` to start the query, but
		// use a non-Eloquent query here to avoid an infinite loop
		// with this query builder.
		return DB::table('albums', 'a')
			->join('photos', 'album_id', '=', 'a.id')
			->whereColumn('a._lft', '>=', 'albums._lft')
			->whereColumn('a._rgt', '<=', 'albums._rgt')
			->whereNotNull('taken_at');
	}

	/**
	 * Apply Visibiltiy conditions.
	 * This a simplified version of AlbumQueryPolicy::applyVisibilityFilter().
	 *
	 * @param Builder $countQuery
	 *
	 * @return Builder Query with the visibility requirements applied
	 */
	private function applyVisibilityConditioOnSubalbums(Builder $countQuery, AlbumQueryPolicy $albumQueryPolicy): Builder
	{
		if (Auth::user()?->may_administrate === true) {
			return $countQuery;
		}

		$userID = Auth::id();

		// Only join with base_album (used to get owner_id) when user is logged in
		$countQuery->when(Auth::check(),
			fn ($q) => $albumQueryPolicy->joinBaseAlbumOwnerId(
				query: $q,
				second: 'a.id',
				full: false,
			)
		);

		// We must left join with `conputed_access_permissions`.
		// We must restrict the `LEFT JOIN` to the user ID which
		// is also used in the outer `WHERE`-clause.
		// See `applyVisibilityFilter` and `appendAccessibilityConditions`
		// in AlbumQueryPolicy.
		$albumQueryPolicy->joinSubComputedAccessPermissions(
			query: $countQuery,
			second: 'a.id',
			type: 'left',
		);

		// We must wrap everything into an outer query to avoid any undesired
		// effects in case that the original query already contains an
		// "OR"-clause.
		// The sub-query only uses properties (i.e. columns) which are
		// defined on the common base model for all albums.
		$visibilitySubQuery = function (Builder $query2) use ($userID) {
			$query2
				// We laverage that IS_LINK_REQUIRED is NULL if the album is NOT shared publically (left join).
				->where(APC::COMPUTED_ACCESS_PERMISSIONS . '.' . APC::IS_LINK_REQUIRED, '=', false)
				->when($userID !== null,
					// Current user is the owner of the album
					fn ($q) => $q
						->orWhere('base_albums.owner_id', '=', $userID));
		};

		return $countQuery->where($visibilitySubQuery);
	}

	/**
	 * Apply Visibiltiy conditions.
	 * This a simplified version of PhotoQueryPolicy::applyVisibilityFilter().
	 *
	 * @param Builder $countQuery
	 *
	 * @return Builder Query with the visibility requirements applied
	 */
	private function applyVisibilityConditioOnPhotos(Builder $countQuery, AlbumQueryPolicy $albumQueryPolicy): Builder
	{
		if (Auth::user()?->may_administrate === true) {
			return $countQuery;
		}

		$userID = Auth::id();

		// Only join with base_album (used to get owner_id) when user is logged in
		$countQuery->when($userID !== null,
			fn ($q) => $albumQueryPolicy->joinBaseAlbumOwnerId(
				query: $q,
				second: 'p.album_id',
				full: false,
			)
		);

		$albumQueryPolicy->joinSubComputedAccessPermissions(
			query: $countQuery,
			second: 'p.album_id',
			type: 'left',
		);

		// We must wrap everything into an outer query to avoid any undesired
		// effects in case that the original query already contains an
		// "OR"-clause.
		$visibilitySubQuery = function ($query2) use ($userID) {
			$query2
				->where(APC::COMPUTED_ACCESS_PERMISSIONS . '.' . APC::IS_LINK_REQUIRED, '=', false)
				->when($userID !== null,
					fn ($query) => $query
						->orWhere('base_albums.owner_id', '=', $userID)
						->orWhere('p.owner_id', '=', $userID)
				);
		};

		return $countQuery->where($visibilitySubQuery);
	}

	/**
	 * Scope limits query to select just root node.
	 *
	 * @return AlbumBuilder
	 */
	public function whereIsRoot(): AlbumBuilder
	{
		$this->query->whereNull($this->model->getParentIdName());

		return $this;
	}
}
