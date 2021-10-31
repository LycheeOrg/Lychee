<?php

namespace Kalnoy\Nestedset;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Accompanies {@link \Kalnoy\Nestedset\NodeTrait}.
 *
 * This interface declares all public methods of a node which are implemented
 * by {@link \Kalnoy\Nestedset\NodeTrait}.
 *
 * Every model which represents a node in a nested set, must realize this
 * interface.
 * This interface is mandatory such that
 * {@link \Kalnoy\Nestedset\NestedSet::isNode()} recognizes an object as a
 * node.
 */
interface Node
{
	/**
	 * Relation to the parent.
	 *
	 * @return BelongsTo
	 */
	public function parent();

	/**
	 * Relation to children.
	 *
	 * @return HasMany
	 */
	public function children();

	/**
	 * Get query for descendants of the node.
	 *
	 * @return DescendantsRelation
	 */
	public function descendants();

	/**
	 * Get query for siblings of the node.
	 *
	 * @return QueryBuilder
	 */
	public function siblings();

	/**
	 * Get the node siblings and the node itself.
	 *
	 * @return QueryBuilder
	 */
	public function siblingsAndSelf();

	/**
	 * Get query for the node siblings and the node itself.
	 *
	 * @param array $columns
	 *
	 * @return EloquentCollection
	 */
	public function getSiblingsAndSelf(array $columns = ['*']);

	/**
	 * Get query for siblings after the node.
	 *
	 * @return QueryBuilder
	 */
	public function nextSiblings();

	/**
	 * Get query for siblings before the node.
	 *
	 * @return QueryBuilder
	 */
	public function prevSiblings();

	/**
	 * Get query for nodes after current node.
	 *
	 * @return QueryBuilder
	 */
	public function nextNodes();

	/**
	 * Get query for nodes before current node in reversed order.
	 *
	 * @return QueryBuilder
	 */
	public function prevNodes();

	/**
	 * Get query ancestors of the node.
	 *
	 * @return AncestorsRelation
	 */
	public function ancestors();

	/**
	 * Make this node a root node.
	 *
	 * @return $this
	 */
	public function makeRoot();

	/**
	 * Save node as root.
	 *
	 * @return bool
	 */
	public function saveAsRoot();

	/**
	 * @param $lft
	 * @param $rgt
	 * @param $parentId
	 *
	 * @return $this
	 */
	public function rawNode($lft, $rgt, $parentId);

	/**
	 * Move node up given amount of positions.
	 *
	 * @param int $amount
	 *
	 * @return bool
	 */
	public function up(int $amount = 1);

	/**
	 * Move node down given amount of positions.
	 *
	 * @param int $amount
	 *
	 * @return bool
	 */
	public function down(int $amount = 1);

	/**
	 * @since 2.0
	 */
	public function newEloquentBuilder($query);

	/**
	 * Get a new base query that includes deleted nodes.
	 *
	 * @since 1.1
	 *
	 * @return QueryBuilder
	 */
	public function newNestedSetQuery($table = null);

	/**
	 * @param ?string $table
	 *
	 * @return QueryBuilder
	 */
	public function newScopedQuery(?string $table = null);

	/**
	 * @param mixed   $query
	 * @param ?string $table
	 *
	 * @return mixed
	 */
	public function applyNestedSetScope($query, ?string $table = null);

	/**
	 * @param array $attributes
	 *
	 * @return self
	 */
	public static function scoped(array $attributes);

	public function newCollection(array $models = []);

	/**
	 * Get node height (rgt - lft + 1).
	 *
	 * @return int
	 */
	public function getNodeHeight();

	/**
	 * Get number of descendant nodes.
	 *
	 * @return int
	 */
	public function getDescendantCount();

	/**
	 * Set the value of model's parent id key.
	 *
	 * Behind the scenes node is appended to found parent node.
	 *
	 * @param int $value
	 *
	 * @throws \Exception If parent node doesn't exists
	 */
	public function setParentIdAttribute(int $value);

	/**
	 * Get whether node is root.
	 *
	 * @return bool
	 */
	public function isRoot();

	/**
	 * @return bool
	 */
	public function isLeaf();

	/**
	 * Get the lft key name.
	 *
	 * @return string
	 */
	public function getLftName();

	/**
	 * Get the rgt key name.
	 *
	 * @return string
	 */
	public function getRgtName();

	/**
	 * Get the parent id key name.
	 *
	 * @return string
	 */
	public function getParentIdName();

	/**
	 * Get the value of the model's lft key.
	 *
	 * @return int
	 */
	public function getLft();

	/**
	 * Get the value of the model's rgt key.
	 *
	 * @return int
	 */
	public function getRgt();

	/**
	 * Get the value of the model's parent id key.
	 *
	 * @return int
	 */
	public function getParentId();

	/**
	 * Returns node that is next to current node without constraining to siblings.
	 *
	 * This can be either a next sibling or a next sibling of the parent node.
	 *
	 * @param array $columns
	 *
	 * @return self
	 */
	public function getNextNode(array $columns = ['*']);

	/**
	 * Returns node that is before current node without constraining to siblings.
	 *
	 * This can be either a prev sibling or parent node.
	 *
	 * @param array $columns
	 *
	 * @return self
	 */
	public function getPrevNode(array $columns = ['*']);

	/**
	 * @param array $columns
	 *
	 * @return Collection
	 */
	public function getAncestors(array $columns = ['*']);

	/**
	 * @param array $columns
	 *
	 * @return Collection|self[]
	 */
	public function getDescendants(array $columns = ['*']);

	/**
	 * @param array $columns
	 *
	 * @return Collection|self[]
	 */
	public function getSiblings(array $columns = ['*']);

	/**
	 * @param array $columns
	 *
	 * @return Collection<self>
	 */
	public function getNextSiblings(array $columns = ['*']);

	/**
	 * @param array $columns
	 *
	 * @return Collection<self>
	 */
	public function getPrevSiblings(array $columns = ['*']);

	/**
	 * @param array $columns
	 *
	 * @return Node
	 */
	public function getNextSibling(array $columns = ['*']);

	/**
	 * @param array $columns
	 *
	 * @return Node
	 */
	public function getPrevSibling(array $columns = ['*']);

	/**
	 * @return array<int>
	 */
	public function getBounds();

	/**
	 * @param $value
	 *
	 * @return $this
	 */
	public function setLft($value);

	/**
	 * @param $value
	 *
	 * @return $this
	 */
	public function setRgt($value);

	/**
	 * @param $value
	 *
	 * @return $this
	 */
	public function setParentId($value);

	/**
	 * @param array|null $except
	 *
	 * @return $this
	 */
	public function replicate(array $except = null);
}
