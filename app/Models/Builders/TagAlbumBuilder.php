<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models\Builders;

use App\Eloquent\FixedQueryBuilder;
use App\Exceptions\Internal\QueryBuilderException;
use App\Models\TagAlbum;
use Illuminate\Support\Facades\DB;

/**
 * Specialized query builder for {@link \App\Models\TagAlbum}.
 *
 * This query builder adds the "virtual" columns `max_taken_at`,
 * `min_taken_at`, and `is_shared_with_current_user`
 * if actual models are hydrated from the DB.
 * Using a custom query builder rather than a global scope enables more
 * fine-grained control, when the columns are added.
 * A global scope is always added to the query, even if the query is only
 * used as a sub-query which will not hydrate actual models.
 * Thus, a global scope unnecessarily complicates queries in many cases.
 *
 * @extends FixedQueryBuilder<\App\Models\TagAlbum>
 */
class TagAlbumBuilder extends FixedQueryBuilder
{
	/**
	 * Get the hydrated models without eager loading.
	 *
	 * @param array<string>|string $columns
	 *
	 * @return array<int,TagAlbum>
	 *
	 * @throws QueryBuilderException
	 */
	public function getModels($columns = ['*']): array
	{
		$baseQuery = $this->getQuery();
		if (($baseQuery->columns === null || count($baseQuery->columns) === 0) && is_string($baseQuery->from)) {
			$this->select([$baseQuery->from . '.*']);
		}

		if (
			($columns === ['*'] || $columns === ['tag_albums.*']) &&
			($baseQuery->columns === ['*'] || $baseQuery->columns === ['tag_albums.*'])
		) {
			$this->addSelect([
				DB::raw('null as max_taken_at'),
				DB::raw('null as min_taken_at'),
			]);
		}

		return parent::getModels($columns);
	}
}
