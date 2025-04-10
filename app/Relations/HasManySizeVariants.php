<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Relations;

use App\Contracts\Exceptions\InternalLycheeException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Models\Extensions\SizeVariants;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\InvalidCastException;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @extends HasMany<SizeVariant,Photo>
 */
class HasManySizeVariants extends HasMany
{
	public function __construct(Photo $owning_photo)
	{
		parent::__construct(
			SizeVariant::query(),
			$owning_photo,
			'photo_id',
			'id'
		);
	}

	/**
	 * Get the results of the relationship.
	 *
	 * Phpstan is complaining about:
	 * Return type (App\Models\Extensions\SizeVariants) of method App\Relations\HasManySizeVariants::getResults()
	 * should be compatible with return type (Illuminate\Database\Eloquent\Collection<int,App\Models\SizeVariant>)
	 * of method Illuminate\Database\Eloquent\Relations\HasMany<App\Models\SizeVariant,App\Models\Photo>::getResults()
	 *
	 * In this specific case we are voluntarily breaking the Liskov Substitution Principle.
	 *
	 * @return SizeVariants
	 *
	 * @phpstan-ignore method.childReturnType
	 */
	public function getResults(): SizeVariants
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
	 * @param Photo[] $models
	 * @param string  $relation
	 *
	 * @return array<int,Photo>
	 */
	public function initRelation(array $models, $relation): array
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
	 * @param Photo[]                     $models   an array of parent models
	 * @param Collection<int,SizeVariant> $results  the unified collection of all child models of all parent models
	 * @param string                      $relation the name of the relation from the parent to the child models
	 *
	 * @return array<int,Photo>
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
				/** @var Collection<int,SizeVariant> $children_of_model */
				$children_of_model = $this->getRelationValue($dictionary, $key, 'many');
				$model->setRelation($relation, new SizeVariants($model, $children_of_model));
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
	 *
	 * @throws InternalLycheeException
	 */
	protected function setForeignAttributesForCreate(Model $model)
	{
		try {
			if (!($model instanceof SizeVariant)) {
				throw new LycheeInvalidArgumentException('model must be an instance of SizeVariant');
			}
			$model->setAttribute('photo_id', $this->getParentKey());
			$model->setRelation('photo', $this->parent);
		} catch (EncryptException|InvalidCastException|JsonEncodingException $e) {
			// thrown by Eloquent\Model::setAttribute
			throw new FrameworkException('Eloquent\'s model', $e);
		}
	}
}