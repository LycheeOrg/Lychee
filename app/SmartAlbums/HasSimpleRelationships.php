<?php

namespace App\SmartAlbums;

/**
 * Trait HasSimpleRelationships.
 *
 * This trait is a copy of
 * {@link \Illuminate\Database\Eloquent\Concerns\HasRelationships}
 * stripped down to the essential methods which are required by a class to
 * own relationships without being a full model.
 */
trait HasSimpleRelationships
{
	/**
	 * The loaded relationships for the model.
	 *
	 * @var array
	 */
	protected array $relations = [];

	/**
	 * The relation resolver callbacks.
	 *
	 * @var array
	 */
	protected static array $relationResolvers = [];

	/**
	 * Define a dynamic relation resolver.
	 *
	 * @param string   $name
	 * @param \Closure $callback
	 *
	 * @return void
	 */
	public static function resolveRelationUsing(string $name, \Closure $callback): void
	{
		static::$relationResolvers = array_replace_recursive(
			static::$relationResolvers,
			[static::class => [$name => $callback]]
		);
	}

	/**
	 * Get all the loaded relations for the instance.
	 *
	 * @return array
	 */
	public function getRelations()
	{
		return $this->relations;
	}

	/**
	 * Get a specified relationship.
	 *
	 * @param string $relation
	 *
	 * @return mixed
	 */
	public function getRelation(string $relation)
	{
		return $this->relations[$relation];
	}

	/**
	 * Determine if the given relation is loaded.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function relationLoaded(string $key): bool
	{
		return array_key_exists($key, $this->relations);
	}

	/**
	 * Set the given relationship on the model.
	 *
	 * @param string $relation
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public function setRelation(string $relation, $value): self
	{
		$this->relations[$relation] = $value;

		return $this;
	}

	/**
	 * Unset a loaded relationship.
	 *
	 * @param string $relation
	 *
	 * @return $this
	 */
	public function unsetRelation(string $relation): self
	{
		unset($this->relations[$relation]);

		return $this;
	}

	/**
	 * Set the entire relations array on the model.
	 *
	 * @param array $relations
	 *
	 * @return $this
	 */
	public function setRelations(array $relations): self
	{
		$this->relations = $relations;

		return $this;
	}

	/**
	 * Unset all the loaded relations for the instance.
	 *
	 * @return $this
	 */
	public function unsetRelations(): self
	{
		$this->relations = [];

		return $this;
	}
}