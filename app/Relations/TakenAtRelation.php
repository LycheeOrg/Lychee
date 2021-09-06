<?php

namespace App\Relations;

use App\Assets\CarbonSpan;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\MultipleRecordsFoundException;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Support\Collection as BaseCollection;

/**
 * This relation associates an album with a timespan of the least and most
 * recent photo within the album and its sub-albums.
 *
 * Assume that the IDs of the albums in a2 are the albums under consideration.
 * This relation runs the following DB query:
 *
 *     SELECT
 *       MIN(taken_at),
 *       MAX(taken_at),
 *       a2.id
 *     FROM photos
 *     JOIN albums AS a1 ON photos.album_id = a1.id
 *     RIGHT JOIN albums AS a2 ON (a1._lft >= a2._lft AND a1._rgt <= a2._rgt)
 *     WHERE a2.id IN (...)
 *     GROUP BY a2.id
 *     ORDER BY a2.id;
 *
 * Firstly, the query first joins all photos with their direct parent albums
 * (aliased as `a1`) in order to associate each photo with its immediate
 * `_lft` and `_rgt` values.
 * Secondly, the query does a self join on the albums such that each album
 * is associated with its parent albums (aliased as `a2`).
 * Thirdly, the query filters for the parent albums of interest.
 * The ID of the parent album must be included as a result column to enable
 * matching the results to a set of albums, if the albums are loaded eagerly.
 */
class TakenAtRelation extends Relation
{
	public function __construct(Album $parent)
	{
		parent::__construct(
			(new Photo())->newModelQuery()
				->join('albums as a1', 'photos.album_id', '=', 'a1.id')
				->join('albums as a2', function (JoinClause $join) {
					$join
						->on('a1._lft', '>=', 'a2._lft')
						->on('a1._rgt', '<=', 'a2._rgt');
				})
				->selectRaw('a2.id AS album_id, MIN(photos.taken_at) AS min_taken_at, MAX(photos.taken_at) AS max_taken_at')
				->groupBy('a2.id')
				->orderBy('a2.id'),
			$parent
		);
	}

	public function addConstraints()
	{
		if (static::$constraints) {
			$this->query->where('a2.id', '=', $this->parent->getKey());
		}
	}

	public function addEagerConstraints(array $models)
	{
		$this->query->whereIn('a2.id', $this->getKeys($models));
	}

	public function initRelation(array $models, $relation): array
	{
		foreach ($models as $model) {
			$model->setRelation($relation, null);
		}

		return $models;
	}

	public function match(array $models, BaseCollection $results, $relation): array
	{
		$dictionary = $results->mapToDictionary(fn ($result) => [$result->album_id => $result]);
		/** @var Album $model */
		foreach ($models as $model) {
			$albumID = $model->getKey();
			if (isset($dictionary[$albumID])) {
				$item = $dictionary[$albumID][0];
				$min = $this->related->asDateTime($item->min_taken_at);
				$max = $this->related->asDateTime($item->max_taken_at);
				if ($min === false || $min === null || $max === false || $max === null) {
					$model->setRelation($relation, null);
				} else {
					$model->setRelation($relation, new CarbonSpan($min, $max));
				}
			} else {
				$model->setRelation($relation, null);
			}
		}

		return $models;
	}

	public function getResults(): ?CarbonSpan
	{
		$results = $this->getBaseQuery()->get();
		if (!$results->containsOneItem()) {
			// This should never happen, just a sanity check
			throw new \RuntimeException('query returned ambiguous results');
		}
		$item = $results->pop();
		if ($item->album_id != $this->parent->getKey()) {
			// This should never happen, just a sanity check
			throw new \RuntimeException('query returned result for wrong album');
		}

		$min = $this->related->asDateTime($item->min_taken_at);
		$max = $this->related->asDateTime($item->max_taken_at);
		if ($min === false || $min === null || $max === false || $max === null) {
			return null;
		}

		return new CarbonSpan($min, $max);
	}

	/**
	 * Get the relationship for eager loading.
	 *
	 * @return BaseCollection
	 */
	public function getEager(): BaseCollection
	{
		return $this->getBaseQuery()->get();
	}

	/**
	 * Execute the query and get the first result if it's the sole matching record.
	 *
	 * @param array|string $columns
	 *
	 * @return array
	 *
	 * @throws RecordsNotFoundException
	 * @throws MultipleRecordsFoundException
	 */
	public function sole($columns = ['*']): array
	{
		$result = $this->getBaseQuery()->take(2)->get($columns);

		if ($result->isEmpty()) {
			throw (new RecordsNotFoundException());
		}

		if ($result->count() > 1) {
			throw new MultipleRecordsFoundException();
		}

		return $result->pop();
	}

	/**
	 * Execute the query as a "select" statement.
	 *
	 * @param array $columns
	 *
	 * @return BaseCollection
	 */
	public function get($columns = ['*']): BaseCollection
	{
		return $this->getBaseQuery()->get($columns);
	}

	/**
	 * Touch all the related models for the relationship.
	 *
	 * @return void
	 */
	public function touch()
	{
	}

	/**
	 * Run a raw update against the base query.
	 *
	 * @param array $attributes
	 *
	 * @return int
	 */
	public function rawUpdate(array $attributes = []): int
	{
		return 0;
	}
}
