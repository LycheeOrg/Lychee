<?php

namespace App\Actions\Photo;

use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Photo;

/**
 * This class updates a property of a MULTIPLE photos at the same time.
 * As a result, the do function takes as input an array containing the desired photoIDs.
 *
 * This will NOT CRASH if one of the photoID is incorrect due to the nature of the SQL query.
 */
class Setters
{
	public string $property;

	/**
	 * @throws QueryBuilderException
	 */
	public function do(array $photoIDs, ?string $value): void
	{
		try {
			Photo::query()
				->whereIn('id', $photoIDs)
				->update([$this->property => $value]);
		} catch (\InvalidArgumentException $e) {
			throw new QueryBuilderException($e);
		}
	}
}
