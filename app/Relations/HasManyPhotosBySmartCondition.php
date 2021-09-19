<?php

namespace App\Relations;

use App\Contracts\InternalLycheeException;
use App\Exceptions\Internal\NotImplementedException;
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
	 *
	 * @throws InternalLycheeException
	 */
	public function addConstraints()
	{
		$this->photoAuthorisationProvider
			->applyVisibilityFilter($this->query)
			->where($this->smartCondition);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws NotImplementedException
	 */
	public function addEagerConstraints(array $models)
	{
		throw new NotImplementedException('built-in smart albums do not support eager loading (they are no models)');
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws NotImplementedException
	 */
	public function match(array $models, Collection $results, $relation)
	{
		throw new NotImplementedException('built-in smart albums do not support eager loading (they are no models)');
	}
}
