<?php

namespace App\Models\Extensions;

use App\Contracts\InternalLycheeException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\QueryBuilder;

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
class AlbumBuilder extends QueryBuilder
{
	/**
	 * Get the hydrated models without eager loading.
	 *
	 * @param array|string $columns
	 *
	 * @return Model[]|static[]
	 *
	 * @throws InternalLycheeException
	 */
	public function getModels($columns = ['*'])
	{
		try {
			$baseQuery = $this->getQuery();
			if ($columns == ['*'] && ($baseQuery->columns == ['*'] || $baseQuery->columns == null)) {
				$this->addSelect([
					'min_taken_at' => Photo::query()
						->select('taken_at')
						->join('albums as a', 'a.id', '=', 'album_id')
						->whereColumn('a._lft', '>=', 'albums._lft')
						->whereColumn('a._rgt', '<=', 'albums._rgt')
						->whereNotNull('taken_at')
						->orderBy('taken_at', 'asc')
						->limit(1),
					'max_taken_at' => Photo::query()
						->select('taken_at')
						->join('albums as a', 'a.id', '=', 'album_id')
						->whereColumn('a._lft', '>=', 'albums._lft')
						->whereColumn('a._rgt', '<=', 'albums._rgt')
						->whereNotNull('taken_at')
						->orderBy('taken_at', 'desc')
						->limit(1),
				]);
			}

			return parent::getModels($columns);
		} catch (\InvalidArgumentException $e) {
			throw new QueryBuilderException($e);
		}
	}
}
