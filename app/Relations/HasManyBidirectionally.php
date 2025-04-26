<?php

/**
 * SPDX-License-Identifier: MIT
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
	 * @param string                 $foreign_key
	 * @param string                 $local_key
	 * @param string                 $foreign_method_name
	 *
	 * @return void
	 */
	public function __construct(Builder $query, Model $parent, string $foreign_key, string $local_key, string $foreign_method_name)
	{
		parent::__construct($query, $parent, $foreign_key, $local_key);
		$this->foreign_method_name = $foreign_method_name;
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
				/** @var Collection<int,TRelatedModel> $children_of_model */
				$children_of_model = $this->getRelationValue($dictionary, $key, 'many');
				$model->setRelation($relation, $children_of_model);
				// This is the newly added code which sets this method apart
				// from the original method and additionally sets the
				// reverse link
				foreach ($children_of_model as $child_model) {
					$child_model->setRelation($this->foreign_method_name, $model);
				}
			}
		}

		return $models;
	}
}