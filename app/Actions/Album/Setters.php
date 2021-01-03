<?php

namespace App\Actions\Album;

use App\Models\Album;

/**
 * This class updates a property of a MULTIPLE albums at the same time.
 * As a result, the do function takes as input an array containing the desired albumIDs.
 *
 * This will NOT CRASH if one of the albumID is incorrect due to the nature of the SQL query.
 */
class Setters extends Action
{
	public $property;

	public function do(array $albumIDs, string $value): bool
	{
		return Album::whereIn('id', $albumIDs)->update([$this->property => $value]);
	}
}
