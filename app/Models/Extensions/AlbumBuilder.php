<?php

namespace App\Models\Extensions;

use App\Models\Album;
use Illuminate\Support\Facades\DB;
use Kalnoy\Nestedset\QueryBuilder as NSQueryBuilder;

/**
 * Specialized query builder for {@link \App\Models\Album}.
 *
 * This query builder adds the "virtual" columns `max_taken_at` and
 * `min_taken_at`, if actual models are hydrated from the DB.
 * Using a custom query builder rather than a global scope enables more
 * fine-grained control, when the columns are added.
 * A global scope is always added to the query, even if the query is only
 * used as a sub-query which will not hydrate actual models.
 * Thus, a global scope unnecessarily complicates queries in many cases.
 */
class AlbumBuilder extends NSQueryBuilder
{
	/**
	 * Get the hydrated models without eager loading.
	 *
	 * Adds the "virtual" columns min_taken_at and max_taken_at to the query,
	 * if a "full" model is requested, i.e. if the selected columns are
	 * `*` or not given at all.
	 *
	 * @param array|string $columns
	 *
	 * @return Album[]
	 */
	public function getModels($columns = ['*']): array
	{
		$baseQuery = $this->getQuery();
		if (
			($columns == ['*'] || $columns == ['albums.*']) &&
			($baseQuery->columns == ['*'] || $baseQuery->columns == ['albums.*'] || $baseQuery->columns == null)
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

			$this->addSelect([
				'min_taken_at' => $minTsSelect,
				'max_taken_at' => $maxTsSelect,
			]);
		}

		return parent::getModels($columns);
	}
}
