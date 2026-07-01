<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models\Builders;

use App\Eloquent\FixedQueryBuilder;
use App\Exceptions\Internal\QueryBuilderException;
use App\Models\PersonAlbum;
use Illuminate\Support\Facades\DB;

/**
 * Specialized query builder for {@link \App\Models\PersonAlbum}.
 *
 * @template TModelClass of PersonAlbum
 *
 * @extends FixedQueryBuilder<TModelClass>
 */
class PersonAlbumBuilder extends FixedQueryBuilder
{
	/**
	 * @param array<string>|string $columns
	 *
	 * @return list<PersonAlbum>
	 *
	 * @throws QueryBuilderException
	 */
	public function getModels($columns = ['*']): array
	{
		$base_query = $this->getQuery();
		if (($base_query->columns === null || count($base_query->columns) === 0) && is_string($base_query->from)) {
			$this->select([$base_query->from . '.*']);
		}

		if (
			($columns === ['*'] || $columns === ['person_albums.*']) &&
			($base_query->columns === ['*'] || $base_query->columns === ['person_albums.*'])
		) {
			$this->addSelect([
				DB::raw('null as max_taken_at'),
				DB::raw('null as min_taken_at'),
			]);
		}

		return parent::getModels($columns);
	}
}
