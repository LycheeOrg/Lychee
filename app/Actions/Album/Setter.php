<?php

namespace App\Actions\Album;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * This class updates a property of a **single** album.
 * Hence, {@link Setter::do()} takes a single `albumID` as input.
 *
 * The method {@link Setter::do()} **will throw** a
 * {@link ModelNotFoundException}  exception, if `albumID` does not point to
 * an existing album.
 *
 * This is intended behaviour.
 */
class Setter extends Action
{
	private Builder $query;
	private string $property;

	/**
	 * Setter constructor.
	 *
	 * @param string $property the name of the property
	 */
	protected function __construct(Builder $query, string $property)
	{
		parent::__construct();
		$this->query = $query;
		$this->property = $property;
	}

	/**
	 * @param string $albumID the ID of the album
	 * @param mixed  $value   the value to be set
	 *
	 * @throws ModelNotFoundException
	 */
	public function do(string $albumID, $value): void
	{
		if ($this->query
				->where('id', '=', $albumID)
				->update([$this->property => $value]) !== 1
		) {
			throw new ModelNotFoundException();
		}
	}
}
