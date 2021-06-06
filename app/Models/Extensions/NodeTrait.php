<?php

namespace App\Models\Extensions;

use Kalnoy\Nestedset\AncestorsRelation;
use Kalnoy\Nestedset\DescendantsRelation;
use Kalnoy\Nestedset\NodeTrait as BaseNodeTrait;
use Kalnoy\Nestedset\QueryBuilder;

/**
 * Trait NodeTrait.
 *
 * Patches {@link \Kalnoy\Nestedset\NodeTrait} such that it works properly
 * with Eloquent models which register query scopes.
 * This trait can be removed after
 * [PR #514](https://github.com/lazychaser/laravel-nestedset/pull/514)
 * has been merged into master of NestedSet and after the Composer
 * dependencies have been updated.
 */
trait NodeTrait
{
	use BaseNodeTrait;

	/**
	 * Get query for descendants of the node.
	 *
	 * @return DescendantsRelation
	 */
	public function descendants()
	{
		return new DescendantsRelation($this->newQueryWithoutScopes(), $this);
	}

	/**
	 * Get query ancestors of the node.
	 *
	 * @return AncestorsRelation
	 */
	public function ancestors()
	{
		return new AncestorsRelation($this->newQueryWithoutScopes(), $this);
	}

	/**
	 * Get a new base query that includes deleted nodes.
	 *
	 * @since 1.1
	 *
	 * @return QueryBuilder
	 */
	public function newNestedSetQuery($table = null)
	{
		return $this->applyNestedSetScope($this->newQueryWithoutScopes(), $table);
	}

	/**
	 * @param string $table
	 *
	 * @return QueryBuilder
	 */
	public function newScopedQuery($table = null)
	{
		return $this->applyNestedSetScope($this->newQueryWithoutScopes(), $table);
	}
}