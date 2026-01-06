<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models\Builders;

use App\Eloquent\FixedQueryBuilderTrait;
use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Album;
use App\Policies\AlbumQueryPolicy;
use Kalnoy\Nestedset\QueryBuilder as NSQueryBuilder;

/**
 * Specialized query builder for {@link \App\Models\Album}.
 *
 * Note: max_taken_at, min_taken_at, num_children, and num_photos are now
 * physical columns populated by RecomputeAlbumStatsJob, not virtual columns.
 *
 * @template TModelClass of Album
 *
 * @extends NSQueryBuilder<TModelClass>
 */
class AlbumBuilder extends NSQueryBuilder
{
	/** @phpstan-use FixedQueryBuilderTrait<Album> */
	use FixedQueryBuilderTrait;

	/**
	 * Add a virtual column `is_recursive_nsfw` to the query.
	 * This column is true if the album is recursive NSFW, i.e. if there are
	 * any NSFW albums in the hierarchy of the album.
	 *
	 * This is quite an expensive operation, so we do not compute it by default.
	 *
	 * @return AlbumBuilder
	 */
	public function addVirtualIsRecursiveNSFW(): AlbumBuilder
	{
		$album_query_policy = resolve(AlbumQueryPolicy::class);

		$this->addSelect(['is_recursive_nsfw' => function ($q) use ($album_query_policy): void {
			// This is a subquery that checks if the album is recursive NSFW.
			// It will return true if there are any NSFW albums in the hierarchy.
			$query = $album_query_policy->appendRecursiveSensitiveAlbumsCondition($q, null, null)->toRawSql();
			// We fix the boolean value for PostgreSQL.
			// See explanation in the method `fixPgSqlBool`.
			$query = $this->fixPgSqlBool($query);

			// add select exists to the query.
			$q->selectRaw('exists (' . $query . ')');
		}]);

		return $this;
	}

	/**
	 * Laravel in their brilliancy decided that when using normal binding for eloquent queries,
	 * boolean values should be bound properly.
	 *
	 * They also refuses to fix it: https://github.com/laravel/framework/discussions/48035
	 * > "Changing this in Laravel may break some applications. So it is not a good approach to change it."
	 *
	 * However when using ->toRawSql() boolean values are transformed as integer. This works totally fine on MySQL, Sqlite.
	 * But on PostgreSQL, it does not work. This method fixes the query to use the proper boolean value for PostgreSQL.
	 *
	 * Just no comments.
	 *
	 * (also the fact that we do not have a "select exists" in Laravel is really not convenient, by I digress...)
	 *
	 * @param string $query
	 *
	 * @return string
	 */
	private function fixPgSqlBool(string $query): string
	{
		if (config('database.default') !== 'pgsql') {
			return $query;
		}

		return str_replace('"is_nsfw" = 1', '"is_nsfw" = TRUE', $query);
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
	 * Scope limits query to select just root node.
	 */
	public function whereIsRoot(): AlbumBuilder
	{
		$this->query->whereNull($this->model->getParentIdName());

		return $this;
	}
}
