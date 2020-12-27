<?php

namespace App\Models\Extensions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class ThumbsRelation extends Relation
{
	/**
	 * Set the base constraints on the relation query.
	 *
	 * @return void
	 */
	public function addConstraints()
	{
		if (!static::$constraints) {
			return;
		}

		$this->query->whereDescendantOf($this->parent)
			->applyNestedSetScope();
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
		return $models;
	}

	/**
	 * @param QueryBuilder $query
	 * @param Model        $model
	 */
	protected function addEagerConstraint($query, $model)
	{
		$query->orWhereDescendantOf($model);
	}

	/**
	 * Set the constraints for an eager load of the relation.
	 *
	 * @param array $models
	 *
	 * @return void
	 */
	public function addEagerConstraints(array $models)
	{
		// The first model in the array is always the parent, so add the scope constraints based on that model.
		// @link https://github.com/laravel/framework/pull/25240
		// @link https://github.com/lazychaser/laravel-nestedset/issues/351
		optional($models[0])->applyNestedSetScope($this->query);

		$this->query->whereNested(function (Builder $inner) use ($models) {
			// We will use this query in order to apply constraints to the
			// base query builder
			$outer = $this->parent->newQuery()->setQuery($inner);

			foreach ($models as $model) {
				$this->addEagerConstraint($outer, $model);
			}
		});
	}

	/**
	 * @param Model $model
	 * @param $related
	 *
	 * @return mixed
	 */
	protected function matches(Model $model, $related)
	{
		return $related->isDescendantOf($model);
	}

	/**
	 * @param $hash
	 * @param $table
	 * @param $lft
	 * @param $rgt
	 *
	 * @return string
	 */
	protected function relationExistenceCondition($hash, $table, $lft, $rgt)
	{
		return "{$hash}.{$lft} between {$table}.{$lft} + 1 and {$table}.{$rgt}";
	}
}
