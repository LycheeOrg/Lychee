<?php

namespace App\Relations;

use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Database\Eloquent\Collection;

class HasManyPhotosBySmartCondition extends HasManyPhotos
{
	protected \Closure $smartCondition;

	public function __construct(BaseSmartAlbum $owningAlbum, \Closure $smartCondition)
	{
		$this->smartCondition = $smartCondition;
		parent::__construct($owningAlbum);
	}

	/**
	 * {@inheritDoc}
	 */
	public function addConstraints()
	{
		$this->applyVisibilityFilter($this->query)
			->where($this->smartCondition);
	}

	/**
	 * {@inheritDoc}
	 */
	public function addEagerConstraints(array $models)
	{
		throw new \BadMethodCallException('built-in smart albums do not support eager loading (they are no models)');
	}

	/**
	 * {@inheritDoc}
	 */
	public function match(array $models, Collection $results, $relation)
	{
		throw new \BadMethodCallException('built-in smart albums do not support eager loading (they are no models)');
	}
}
