<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Relations;

use App\Contracts\Relations\BidirectionalRelation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends HasMany<TRelatedModel,TDeclaringModel>
 */
class HasManyBidirectionally extends HasMany implements BidirectionalRelation
{
	use BidirectionalRelationTrait;

	/**
	 * @param Builder<TRelatedModel> $query
	 * @param TDeclaringModel        $parent
	 * @param string                 $foreignKey
	 * @param string                 $localKey
	 * @param string                 $foreignMethodName
	 *
	 * @return void
	 */
	public function __construct(Builder $query, Model $parent, string $foreignKey, string $localKey, string $foreignMethodName)
	{
		parent::__construct($query, $parent, $foreignKey, $localKey);
		$this->foreignMethodName = $foreignMethodName;
	}

	/**
	 * Match the eagerly loaded results to their parents.
	 *
	 * This method is identical to
	 * {@link \Illuminate\Database\Eloquent\Relations\HasOneOrMany::matchOneOrMany}
	 * but additionally sets the reverse association of the child object
	 * back to its parent object.
	 *
	 * @param TDeclaringModel[]             $models   an array of parent models
	 * @param Collection<int,TRelatedModel> $results  the unified collection of all child models of all parent models
	 * @param string                        $relation the name of the relation from the parent to the child models
	 *
	 * @return array<int,TDeclaringModel>
	 */
	public function match(array $models, Collection $results, $relation): array
	{
		$dictionary = $this->buildDictionary($results);

		// Once we have the dictionary we can simply spin through the parent models to
		// link them up with their children using the keyed dictionary to make the
		// matching very convenient and easy work. Then we'll just return them.
		foreach ($models as $model) {
			if (isset($dictionary[$key = $this->getDictionaryKey($model->getAttribute($this->localKey))])) {
				/** @var Collection<int,TRelatedModel> $childrenOfModel */
				$childrenOfModel = $this->getRelationValue($dictionary, $key, 'many');
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
