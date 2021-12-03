<?php

namespace App\Relations;

use App\Actions\PhotoAuthorisationProvider;
use App\Models\Album;
use App\Models\Extensions\SortingDecorator;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class HasManyChildPhotos extends HasManyBidirectionally
{
	protected PhotoAuthorisationProvider $photoAuthorisationProvider;

	public function __construct(Album $owningAlbum)
	{
		// Sic! We must initialize attributes of this class before we call
		// the parent constructor.
		// The parent constructor calls `addConstraints` and thus our own
		// attributes must be initialized by then
		$this->photoAuthorisationProvider = resolve(PhotoAuthorisationProvider::class);
		parent::__construct(
			Photo::query(),
			$owningAlbum,
			'album_id',
			'id',
			'album'
		);
	}

	public function addConstraints()
	{
		if (static::$constraints) {
			parent::addConstraints();
			$this->photoAuthorisationProvider->applyVisibilityFilter($this->query);
		}
	}

	public function addEagerConstraints(array $models)
	{
		parent::addEagerConstraints($models);
		$this->photoAuthorisationProvider->applyVisibilityFilter($this->query);
	}

	public function getResults()
	{
		if (is_null($this->getParentKey())) {
			return $this->related->newCollection();
		}

		return (new SortingDecorator($this->query))
			->orderBy('photos.' . $this->parent->sorting_col, $this->parent->sorting_order)
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
		/** @var Album $model */
		foreach ($models as $model) {
			if (isset($dictionary[$key = $this->getDictionaryKey($model->getAttribute($this->localKey))])) {
				/** @var Collection $childrenOfModel */
				$childrenOfModel = $this->getRelationValue($dictionary, $key, 'many');
				$childrenOfModel = $childrenOfModel
					->sortBy($model->sorting_col, SORT_NATURAL | SORT_FLAG_CASE, $model->sorting_order === 'DESC')
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
