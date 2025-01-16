<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models\Extensions;

use App\Contracts\Relations\BidirectionalRelation;
use App\Relations\HasManyBidirectionally;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

trait HasBidirectionalRelationships
{
	/**
	 * Get a relationship value from a method.
	 *
	 * @param string $method
	 *
	 * @return mixed
	 *
	 * @throws \LogicException
	 */
	protected function getRelationshipFromMethod($method): mixed
	{
		// Run original code from HasAttributes::getRelationshipFromMethod

		$relation = $this->$method(); // @phpstan-ignore-line, PhpStan does not like variadic calls

		if (!$relation instanceof Relation) {
			if (is_null($relation)) {
				throw new \LogicException(sprintf('%s::%s must return a relationship instance, but "null" was returned. Was the "return" keyword used?', static::class, $method));
			}
			throw new \LogicException(sprintf('%s::%s must return a relationship instance.', static::class, $method));
		}

		$result = $relation->getResults();
		$this->setRelation($method, $result);

		// Now the additional code
		// We also set the reverse direction of the relation, i.e. each
		// hydrated model points back to this model

		if ($relation instanceof BidirectionalRelation) {
			if ($result instanceof Collection) {
				/** @var Model $model */
				foreach ($result as $model) {
					$model->setRelation($relation->getForeignMethodName(), $this);
				}
			} elseif ($result instanceof Model) {
				$result->setRelation($relation->getForeignMethodName(), $this);
			} else {
				throw new \LogicException(sprintf('$result must either be a collection of models or a model, but got %s', is_object($result) ? get_class($result) : gettype($result)));
			}
		}

		return $result;
	}

	/**
	 * Define a one-to-many relationship.
	 *
	 * Inspired by {@link \Illuminate\Database\Eloquent\Concerns\HasRelationships::hasMany}.
	 *
	 * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
	 *
	 * @param class-string<TRelatedModel> $related
	 * @param string|null                 $foreignKey
	 * @param string|null                 $localKey
	 * @param string|null                 $foreignMethodName
	 *
	 * @return HasManyBidirectionally<TRelatedModel,$this>
	 */
	public function hasManyBidirectionally(string $related, ?string $foreignKey = null, ?string $localKey = null, ?string $foreignMethodName = null): HasManyBidirectionally
	{
		/** @var TRelatedModel $instance */
		$instance = $this->newRelatedInstance($related);

		$foreignKey = $foreignKey ?? $this->getForeignKey();

		$localKey = $localKey ?? $this->getKeyName();

		$foreignMethodName = $foreignMethodName ?? $this->getForeignProperty();

		/** @phpstan-ignore-next-line */
		return $this->newHasManyBidirectionally(
			$instance->newQuery(),
			$this,
			$instance->getTable() . '.' . $foreignKey,
			$localKey,
			$foreignMethodName
		);
	}

	/**
	 * Instantiate a new HasManyBidirectionally relationship.
	 *
	 * Inspired by {@link \Illuminate\Database\Eloquent\Concerns\HasRelationships::newHasMany}.
	 *
	 * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
	 * @template TParentModel of \Illuminate\Database\Eloquent\Model
	 *
	 * @param Builder<TRelatedModel> $query
	 * @param TParentModel           $parent
	 * @param string                 $foreignKey
	 * @param string                 $localKey
	 * @param string                 $foreignMethodName
	 *
	 * @return HasManyBidirectionally<TRelatedModel,TParentModel>
	 */
	protected function newHasManyBidirectionally(Builder $query, Model $parent, string $foreignKey, string $localKey, string $foreignMethodName): HasManyBidirectionally
	{
		return new HasManyBidirectionally($query, $parent, $foreignKey, $localKey, $foreignMethodName);
	}

	/**
	 * Get the default foreign method name for this model.
	 *
	 * @return string
	 */
	public function getForeignProperty(): string
	{
		return Str::snake(class_basename($this));
	}
}
