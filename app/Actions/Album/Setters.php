<?php

namespace App\Actions\Album;

use App\Exceptions\Internal\QueryBuilderException;
use Illuminate\Database\Eloquent\Builder;

/**
 * This class updates a property of **multiple** albums at once.
 * Hence, {@link Setters::do()} takes an array of album IDs as input.
 *
 * The method {@link Setters::do()} **will not** crash if `albumIDs` evaluates
 * to the empty set due to the nature of the SQL query.
 */
class Setters extends Action
{
	private Builder $query;
	private string $property;

	/**
	 * Setters constructor.
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
	 * @param array $albumIDs the IDs of the albums
	 * @param mixed $value    the value to be set
	 *
	 * @throws QueryBuilderException
	 */
	public function do(array $albumIDs, mixed $value): void
	{
		try {
			$this->query
				->whereIn('id', $albumIDs)
				->update([$this->property => $value]);
		} catch (\InvalidArgumentException $e) {
			throw new QueryBuilderException($e);
		}
	}
}
