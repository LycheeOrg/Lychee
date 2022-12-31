<?php

namespace App\Models\Extensions;

use App\Contracts\Exceptions\InternalLycheeException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Album;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Kalnoy\Nestedset\QueryBuilder as NSQueryBuilder;

/**
 * Specialized query builder for {@link \App\Models\Album}.
 *
 * This query builder adds the "virtual" columns `max_taken_at`,
 * `min_taken_at` as well as `num_children` and `num_photos`, if actual
 * models are hydrated from the DB. Using a custom query builder rather
 * than a global scope enables more fine-grained control, when the
 * columns are added.
 * A global scope is always added to the query, even if the query is only
 * used as a sub-query which will not hydrate actual models.
 * Thus, a global scope unnecessarily complicates queries in many cases.
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
	 * @param array|string $columns
	 *
	 * @return Album[]
	 *
	 * @throws InternalLycheeException
	 */
	public function getModels($columns = ['*']): array
	{
		$baseQuery = $this->getQuery();
		if (
			($columns === ['*'] || $columns === ['albums.*']) &&
			($baseQuery->columns === ['*'] || $baseQuery->columns === ['albums.*'] || $baseQuery->columns === null)
		) {
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
			$minTsSelect = DB::table('albums', 'a')
				->select(DB::raw('MIN(taken_at)'))
				->join('photos', 'album_id', '=', 'a.id')
				->whereColumn('a._lft', '>=', 'albums._lft')
				->whereColumn('a._rgt', '<=', 'albums._rgt')
				->whereNotNull('taken_at');

			$maxTsSelect = $minTsSelect->clone()->select(DB::raw('MAX(taken_at)'));

			$isAdmin = (Auth::user()?->may_administrate === true);
			$userID = Auth::id();

			$countChildren = DB::table('albums', 'a');
			$countChildren->select(DB::raw('COUNT(*)'));
			$countChildren->whereColumn('a.parent_id', '=', 'albums.id');

			$countPhotos =
				DB::table('photos', 'p')
				->select(DB::raw('COUNT(*)'))
				->whereColumn('p.album_id', '=', 'albums.id');

			$this->addSelect([
				'min_taken_at' => $minTsSelect,
				'max_taken_at' => $maxTsSelect,
				'num_children' => $this->applyVisibilityConditioOnSubalbums($countChildren, $isAdmin, $userID),
				'num_photos' => $this->applyVisibilityConditioOnPhotos($countPhotos, $isAdmin, $userID),
			]);
		}

		// The parent method returns a `Model[]`, but we must return
		// `Album[]` and we know that this is indeed the case as we have
		// queried for albums
		// @phpstan-ignore-next-line
		return parent::getModels($columns);
	}

	/**
	 * Get statistics of errors of the tree.
	 *
	 * @return array
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
	 * Apply Visibiltiy conditions.
	 * This a simplified version of AlbumQueryPolicy::applyVisibilityFilter().
	 *
	 * @param Builder         $count
	 * @param bool            $isAdmin
	 * @param int|string|null $userID
	 *
	 * @return Builder Query with the visibility requirements applied
	 */
	private function applyVisibilityConditioOnSubalbums(Builder $count, bool $isAdmin, int|string|null $userID): Builder
	{
		if ($isAdmin) {
			return $count;
		}

		$count->join('base_albums', 'base_albums.id', '=', 'a.id');

		if ($userID !== null) {
			// We must left join with `user_base_album` if and only if we
			// restrict the eventual query to the ID of the authenticated
			// user by a `WHERE`-clause.
			// If we were doing a left join unconditionally, then some
			// albums might appear multiple times as part of the result
			// because an album might be shared with more than one user.
			// Hence, we must restrict the `LEFT JOIN` to the user ID which
			// is also used in the outer `WHERE`-clause.
			// See `applyVisibilityFilter` and `appendAccessibilityConditions`
			// in AlbumQueryPolicy.
			$count->leftJoin(
				'user_base_album',
				function (JoinClause $join) use ($userID) {
					$join
						->on('user_base_album.base_album_id', '=', 'base_albums.id')
						->where('user_base_album.user_id', '=', $userID);
				}
			);
		}
		// We must wrap everything into an outer query to avoid any undesired
		// effects in case that the original query already contains an
		// "OR"-clause.
		// The sub-query only uses properties (i.e. columns) which are
		// defined on the common base model for all albums.
		$visibilitySubQuery = function ($query2) use ($userID) {
			$query2
				->where(
					fn ($q) => $q
						->where('base_albums.is_link_required', '=', false)
						->where('base_albums.is_public', '=', true)
				);
			if ($userID !== null) {
				$query2
					->orWhere('base_albums.owner_id', '=', $userID)
					->orWhere('user_base_album.user_id', '=', $userID);
			}
		};
		$count->where($visibilitySubQuery);

		return $count;
	}

	/**
	 * Apply Visibiltiy conditions.
	 * This a simplified version of PhotoQueryPolicy::applyVisibilityFilter().
	 *
	 * @param Builder         $count
	 * @param bool            $isAdmin
	 * @param int|string|null $userID
	 *
	 * @return Builder Query with the visibility requirements applied
	 */
	private function applyVisibilityConditioOnPhotos(Builder $count, bool $isAdmin, int|string|null $userID): Builder
	{
		if ($isAdmin) {
			return $count;
		}

		$count->join('base_albums', 'base_albums.id', '=', 'p.album_id');

		if ($userID !== null) {
			// We must left join with `user_base_album` if and only if we
			// restrict the eventual query to the ID of the authenticated
			// user by a `WHERE`-clause.
			// If we were doing a left join unconditionally, then some
			// albums might appear multiple times as part of the result
			// because an album might be shared with more than one user.
			// Hence, we must restrict the `LEFT JOIN` to the user ID which
			// is also used in the outer `WHERE`-clause.
			// See `applyVisibilityFilter` and `appendAccessibilityConditions`
			// in AlbumQueryPolicy and PhotoQueryPolicy.
			$count->leftJoin(
				'user_base_album',
				function (JoinClause $join) use ($userID) {
					$join
						->on('user_base_album.base_album_id', '=', 'base_albums.id')
						->where('user_base_album.user_id', '=', $userID);
				}
			);
		}

		// We must wrap everything into an outer query to avoid any undesired
		// effects in case that the original query already contains an
		// "OR"-clause.
		$visibilitySubQuery = function ($query2) use ($userID) {
			$query2->where(
				fn ($q) => $q
					->where('base_albums.is_link_required', '=', false)
					->where('base_albums.is_public', '=', true)
			);
			if ($userID !== null) {
				$query2
					->orWhere('base_albums.owner_id', '=', $userID)
					->orWhere('user_base_album.user_id', '=', $userID)
					->orWhere('p.owner_id', '=', $userID);
			}
			$query2->orWhere('p.is_public', '=', true);
		};

		return $count->where($visibilitySubQuery);
	}
}
