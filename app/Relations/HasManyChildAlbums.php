<?php

namespace App\Relations;

use App\Contracts\Exceptions\InternalLycheeException;
use App\DTO\AlbumSortingCriterion;
use App\Enum\OrderSortingType;
use App\Exceptions\Internal\InvalidOrderDirectionException;
use App\Models\Album;
use App\Models\Builders\AlbumBuilder;
use App\Models\Extensions\SortingDecorator;
use App\Policies\AlbumQueryPolicy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class HasManyChildAlbums extends HasManyBidirectionally
{
	protected AlbumQueryPolicy $albumQueryPolicy;
	private AlbumSortingCriterion $sorting;

	public function __construct(Album $owningAlbum)
	{
		// Sic! We must initialize attributes of this class before we call
		// the parent constructor.
		// The parent constructor calls `addConstraints` and thus our own
		// attributes must be initialized by then
		$this->albumQueryPolicy = resolve(AlbumQueryPolicy::class);
		$this->sorting = AlbumSortingCriterion::createDefault();
		parent::__construct(
			$owningAlbum->newQuery(),
			$owningAlbum,
			'parent_id',
			'id',
			'parent'
		);
	}

	protected function getRelationQuery(): AlbumBuilder
	{
		/**
		 * We know that the internal query is of type `AlbumBuilder`,
		 * because it was set in the constructor as `$owningAlbum->newQuery()`.
		 *
		 * @noinspection PhpIncompatibleReturnTypeInspection
		 *
		 * @phpstan-ignore-next-line
		 */
		return $this->query;
	}

	/**
	 * @throws InternalLycheeException
	 */
	public function addConstraints()
	{
		if (static::$constraints) {
			parent::addConstraints();
			$this->albumQueryPolicy->applyVisibilityFilter($this->getRelationQuery());
		}
	}

	/**
	 * @throws InternalLycheeException
	 */
	public function addEagerConstraints(array $models)
	{
		parent::addEagerConstraints($models);
		$this->albumQueryPolicy->applyVisibilityFilter($this->getRelationQuery());
	}

	/**
	 * @throws InvalidOrderDirectionException
	 */
	public function getResults(): Collection
	{
		if (is_null($this->getParentKey())) {
			return $this->related->newCollection();
		}

		return (new SortingDecorator($this->query))
			->orderBy($this->sorting->column, $this->sorting->order)
			->get();
	}

	/**
	 * Match the eagerly loaded results to their parents.
	 *
	 * @param array      $models   an array of parent models
	 * @param Collection $results  the unified collection of all child models of all parent models
	 * @param string     $relation the name of the relation from the parent to the child models
	 *
	 * @return array
	 */
	public function match(array $models, Collection $results, $relation): array
	{
		$dictionary = $this->buildDictionary($results);

		// Once we have the dictionary we can simply spin through the parent models to
		// link them up with their children using the keyed dictionary to make the
		// matching very convenient and easy work. Then we'll just return them.
		foreach ($models as $model) {
			if (isset($dictionary[$key = $this->getDictionaryKey($model->getAttribute($this->localKey))])) {
				/** @var Collection $childrenOfModel */
				$childrenOfModel = $this->getRelationValue($dictionary, $key, 'many');
				$childrenOfModel = $childrenOfModel
					->sortBy($this->sorting->column->value, SORT_NATURAL | SORT_FLAG_CASE, $this->sorting->order === OrderSortingType::DESC)
					->values();
				$model->setRelation($relation, $childrenOfModel);
				// This is the newly added code which sets this method apart
				// from the original method and additionally sets the
				// reverse link
				/** @var Model $childModel */
				foreach ($childrenOfModel as $childModel) {
					$childModel->setRelation($this->foreignMethodName, $model);
				}
			}
		}

		return $models;
	}
}
