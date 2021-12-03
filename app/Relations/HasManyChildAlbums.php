<?php

namespace App\Relations;

use App\Actions\AlbumAuthorisationProvider;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Extensions\SortingDecorator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class HasManyChildAlbums extends HasManyBidirectionally
{
	protected AlbumAuthorisationProvider $albumAuthorisationProvider;
	private string $sortingCol;
	private string $sortingOrder;

	public function __construct(Album $owningAlbum)
	{
		// Sic! We must initialize attributes of this class before we call
		// the parent constructor.
		// The parent constructor calls `addConstraints` and thus our own
		// attributes must be initialized by then
		$this->albumAuthorisationProvider = resolve(AlbumAuthorisationProvider::class);
		$this->sortingCol = Configs::get_value('sorting_Albums_col');
		$this->sortingOrder = Configs::get_value('sorting_Albums_order');
		parent::__construct(
			$owningAlbum->newQuery(),
			$owningAlbum,
			'parent_id',
			'id',
			'parent'
		);
	}

	public function addConstraints()
	{
		if (static::$constraints) {
			parent::addConstraints();
			$this->albumAuthorisationProvider->applyVisibilityFilter($this->query);
		}
	}

	public function addEagerConstraints(array $models)
	{
		parent::addEagerConstraints($models);
		$this->albumAuthorisationProvider->applyVisibilityFilter($this->query);
	}

	public function getResults()
	{
		if (is_null($this->getParentKey())) {
			return $this->related->newCollection();
		}

		return (new SortingDecorator($this->query))
			->orderBy($this->sortingCol, $this->sortingOrder)
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

		$sortingCol = Configs::get_value('sorting_Albums_col');
		$sortingOrder = Configs::get_value('sorting_Albums_order');

		// Once we have the dictionary we can simply spin through the parent models to
		// link them up with their children using the keyed dictionary to make the
		// matching very convenient and easy work. Then we'll just return them.
		foreach ($models as $model) {
			if (isset($dictionary[$key = $this->getDictionaryKey($model->getAttribute($this->localKey))])) {
				/** @var Collection $childrenOfModel */
				$childrenOfModel = $this->getRelationValue($dictionary, $key, 'many');
				$childrenOfModel = $childrenOfModel
					->sortBy($sortingCol, SORT_NATURAL | SORT_FLAG_CASE, $sortingOrder === 'DESC')
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