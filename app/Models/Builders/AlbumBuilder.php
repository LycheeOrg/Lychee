<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models\Builders;

use App\Constants\AccessPermissionConstants as APC;
use App\Contracts\Exceptions\InternalLycheeException;
use App\Eloquent\FixedQueryBuilder;
use App\Eloquent\FixedQueryBuilderTrait;
use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Album;
use App\Models\Configs;
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
 *
 * @template TModelClass of Album
 * @extends NSQueryBuilder<TModelClass>
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
		$album_query_policy = resolve(AlbumQueryPolicy::class);
		$base_query = $this->getQuery();

		if (
			($columns === ['*'] || $columns === ['albums.*']) &&
			($base_query->columns === ['*'] || $base_query->columns === ['albums.*'] || $base_query->columns === null)
		) {
			$count_children = DB::table('albums', 'a')
				->selectRaw('COUNT(*)')
				->whereColumn('a.parent_id', '=', 'albums.id');

			$count_photos = DB::table('photos', 'p')
				->selectRaw('COUNT(*)')
				->whereColumn('p.album_id', '=', 'albums.id');

			$this->addSelect([
				'min_taken_at' => $this->getTakenAtSQL()->selectRaw('MIN(taken_at)'),
				'max_taken_at' => $this->getTakenAtSQL()->selectRaw('MAX(taken_at)'),
				'num_children' => $this->applyVisibilityConditioOnSubalbums($count_children, $album_query_policy),
				'num_photos' => $this->applyVisibilityConditioOnPhotos($count_photos, $album_query_policy),
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
	 * @return Builder Query with the visibility requirements applied
	 */
	private function applyVisibilityConditioOnSubalbums(Builder $count_query, AlbumQueryPolicy $album_query_policy): Builder
	{
		if (Auth::user()?->may_administrate === true) {
			return $count_query;
		}

		$user_id = Auth::id();

		// Only join with base_album (used to get owner_id) when user is logged in
		$count_query->when(Auth::check(),
			fn ($q) => $album_query_policy->joinBaseAlbumOwnerId(
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
		$album_query_policy->joinSubComputedAccessPermissions(
			query: $count_query,
			second: 'a.id',
			type: 'left',
		);

		// We must wrap everything into an outer query to avoid any undesired
		// effects in case that the original query already contains an
		// "OR"-clause.
		// The sub-query only uses properties (i.e. columns) which are
		// defined on the common base model for all albums.
		$visibility_sub_query = function (Builder $query2) use ($user_id): void {
			$query2
				// We laverage that IS_LINK_REQUIRED is NULL if the album is NOT shared publically (left join).
				->where(APC::COMPUTED_ACCESS_PERMISSIONS . '.' . APC::IS_LINK_REQUIRED, '=', false)
				->when($user_id !== null,
					// Current user is the owner of the album
					fn ($q) => $q
						->orWhere('base_albums.owner_id', '=', $user_id));
		};

		return $count_query->where($visibility_sub_query);
	}

	/**
	 * Apply Visibiltiy conditions.
	 * This a simplified version of PhotoQueryPolicy::applyVisibilityFilter().
	 *
	 * @param Builder          $countQuery
	 * @param AlbumQueryPolicy $album_query_policy
	 *
	 * @return Builder Query with the visibility requirements applied
	 */
	private function applyVisibilityConditioOnPhotos(Builder $count_query, AlbumQueryPolicy $album_query_policy): Builder
	{
		if (Auth::user()?->may_administrate === true) {
			return $count_query;
		}

		$user_id = Auth::id();

		// Only join with base_album (used to get owner_id) when user is logged in
		$count_query->when($user_id !== null,
			fn ($q) => $album_query_policy->joinBaseAlbumOwnerId(
				query: $q,
				second: 'p.album_id',
				full: false,
			)
		);

		$album_query_policy->joinSubComputedAccessPermissions(
			query: $count_query,
			second: 'p.album_id',
			type: 'left',
		);

		// We must wrap everything into an outer query to avoid any undesired
		// effects in case that the original query already contains an
		// "OR"-clause.
		$visibility_sub_query = function ($query2) use ($user_id): void {
			$query2
				->where(APC::COMPUTED_ACCESS_PERMISSIONS . '.' . APC::IS_LINK_REQUIRED, '=', false)
				->when($user_id !== null,
					fn ($query) => $query
						->orWhere('base_albums.owner_id', '=', $user_id)
						->orWhere('p.owner_id', '=', $user_id)
				);
		};

		return $count_query->where($visibility_sub_query);
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