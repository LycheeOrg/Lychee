<?php

namespace App\Models\Extensions;

use App\Exceptions\Internal\QueryBuilderException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
class TagAlbumBuilder extends Builder
{
	/**
	 * Get the hydrated models without eager loading.
	 *
	 * @param array|string $columns
	 *
	 * @return Model[]|static[]
	 *
	 * @throws QueryBuilderException
	 */
	public function getModels($columns = ['*'])
	{
		$baseQuery = $this->getQuery();
		if ($columns == ['*'] && ($baseQuery->columns == ['*'] || $baseQuery->columns == null)) {
			try {
				$this->addSelect([
					$baseQuery->from . '.*',
					DB::raw('null as max_taken_at'),
					DB::raw('null as min_taken_at'),
				]);
			} catch (\InvalidArgumentException $e) {
				throw new QueryBuilderException($e);
			}
		}

		return parent::getModels($columns);
	}
}
