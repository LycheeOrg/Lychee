<?php

namespace App\Actions\Photo;

use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\ModelDBException;
use App\Models\Photo;

/**
 * This class toggle a boolean property of a MULTIPLE photos at the same time.
 * As a result, the do function takes as input an array containing the desired photoIDs.
 *
 * This will NOT CRASH if one of the photoID is incorrect due to the nature of the SQL query.
 */
class Toggles
{
	public string $property;

	/**
	 * @throws ModelDBException
	 * @throws QueryBuilderException
	 */
	public function do(array $photoIDs): void
	{
		try {
			$photos = Photo::query()->whereIn('id', $photoIDs)->get();
		} catch (\InvalidArgumentException $e) {
			throw new QueryBuilderException($e);
		}
		/** @var Photo $photo */
		foreach ($photos as $photo) {
			$photo->{$this->property} = !($photo->{$this->property});
			$photo->save();
		}
	}
}
