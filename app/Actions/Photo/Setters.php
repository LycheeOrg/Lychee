<?php

namespace App\Actions\Photo;

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

	public function do(array $photoIDs, ?string $value): bool
	{
		return Photo::query()->whereIn('id', $photoIDs)->update([$this->property => $value]);
	}
}
