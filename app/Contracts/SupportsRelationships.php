<?php

namespace App\Contracts;

/**
 * Interface SupportsRelationships.
 *
 * This interface should be implemented by every "thing" which supports
 * relationships, but is not necessarily a DB model (e.g. the smart albums).
 * Note: Every class which extends {@link \Illuminate\Database\Eloquent\Model}
 * or which uses the trait
 * {@link \Illuminate\Database\Eloquent\Concerns\HasRelationships}
 * or uses the trait
 * {@link \App\SmartAlbums\HasSimpleRelationships}
 * automatically fulfills the requirements of this interface.
 * We need this interface, because {@link \App\SmartAlbums\BaseSmartAlbum}
 * and its child classes also support relationships with photos, but are not
 * true models.
 */
interface SupportsRelationships
{
	/**
	 * Get all the loaded relations for the instance.
	 *
	 * @return array
	 */
	public function getRelations();

	/**
	 * Get a specified relationship.
	 *
	 * @param string $relation
	 *
	 * @return mixed
	 */
	public function getRelation(string $relation);

	/**
	 * Determine if the given relation is loaded.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function relationLoaded(string $key);

	/**
	 * Set the given relationship on the model.
	 *
	 * @param string $relation
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public function setRelation(string $relation, $value);

	/**
	 * Unset a loaded relationship.
	 *
	 * @param string $relation
	 *
	 * @return $this
	 */
	public function unsetRelation(string $relation);

	/**
	 * Set the entire relations array on the model.
	 *
	 * @param array $relations
	 *
	 * @return $this
	 */
	public function setRelations(array $relations);

	/**
	 * Unset all the loaded relations for the instance.
	 *
	 * @return $this
	 */
	public function unsetRelations();
}
