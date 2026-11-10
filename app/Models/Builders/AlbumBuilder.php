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
use Kalnoy\Nestedset\QueryBuilder as NSQueryBuilder;

/**
 * Specialized query builder for {@link \App\Models\Album}.
 *
 * Note: max_taken_at, min_taken_at, num_children, and num_photos are now
 * physical columns populated by RecomputeAlbumStatsJob, not virtual columns;
 * is_recursive_nsfw is a physical column kept by SyncRecursiveNsfw.
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
