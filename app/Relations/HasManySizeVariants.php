<?php

namespace App\Relations;

use App\Models\Extensions\SizeVariants;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HasManySizeVariants extends HasMany
{
	public function __construct(Photo $owningPhoto)
	{
		parent::__construct(
			SizeVariant::query(),
			$owningPhoto,
			'photo_id',
			'id'
		);
	}

	/**
	 * Get the results of the relationship.
	 *
	 * @return mixed
	 */
	public function getResults()
	{
		/** @var Photo $parent */
		$parent = $this->parent;

		return new SizeVariants($parent,
			is_null($this->getParentKey()) ?
				$this->related->newCollection() :
				$this->query->get()
		);
	}

	/**
	 * Initialize the relation on a set of models.
	 *
	 * @param array  $models
	 * @param string $relation
	 *
	 * @return array
	 */
	public function initRelation(array $models, $relation)
	{
		/** @var Photo $model */
		foreach ($models as $model) {
			$model->setRelation(
				$relation,
				new SizeVariants($model, $this->related->newCollection())
			);
		}

		return $models;
	}

	/**
	 * Match the eagerly loaded results to their parents.
	 *
	 * This method is identical to
	 * {@link \Illuminate\Database\Eloquent\Relations\HasOneOrMany::matchOneOrMany}
	 * but additionally sets the reverse association of the child object
	 * back to its parent object.
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
		/** @var Photo $model */
		foreach ($models as $model) {
			if (isset($dictionary[$key = $this->getDictionaryKey($model->getAttribute($this->localKey))])) {
				/** @var Collection $childrenOfModel */
				$childrenOfModel = $this->getRelationValue($dictionary, $key, 'many');
				$model->setRelation($relation, new SizeVariants($model, $childrenOfModel));
			}
		}

		return $models;
	}

	/**
	 * Set the foreign ID for creating a related model.
	 *
	 * @param Model $model
	 *
	 * @return void
	 */
	protected function setForeignAttributesForCreate(Model $model)
	{
		if (!($model instanceof SizeVariant)) {
			throw new \InvalidArgumentException('model must be an instance of SizeVariant');
		}
		$model->setAttribute('photo_id', $this->getParentKey());
		$model->setRelation('photo', $this->parent);
	}
}
