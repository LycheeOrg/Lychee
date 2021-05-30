<?php

namespace App\Actions\Photo\Extensions;

use App\Exceptions\JsonError;
use App\Facades\Helpers;
use App\Models\Logs;
use App\Models\Photo;
use Illuminate\Database\QueryException;

trait Save
{
	/**
	 * This function aims to fix the duplicate entry key problem.
	 *
	 * TODO: find where the array to string conversion is...
	 *
	 * @param Photo $photo
	 *
	 * @return string
	 */
	public function save(Photo &$photo): int
	{
		do {
			$retry = false;
			try {
				if (!$photo->save()) {
					throw new JsonError('Could not save photo in database!');
				}
			} catch (QueryException $e) {
				$retry = true;
				$this->recover($e, $photo);
			}
		} while ($retry);

		// return the ID.
		return $photo->id;
	}

	/**
	 * Manage recovery from the Exception.
	 *
	 * @throws JsonError if code is neither 23000 or 23505
	 */
	private function recover(QueryException $e, Photo &$photo)
	{
		$errorCode = $e->getCode();
		if ($errorCode == 23000 || $errorCode == 23505) {
			// houston, we have a duplicate entry problem
			do {
				// Our ids are based on current system time, so
				// wait randomly up to 1s before retrying.
				usleep(rand(0, 1000000));
				$newId = Helpers::generateID();
			} while ($newId === $photo->id);

			$photo->id = $newId;
		} else {
			Logs::error(__METHOD__, __LINE__, 'Something went wrong, error ' . $errorCode . ', ' . $e->getMessage());

			throw new JsonError('Something went wrong, error' . $errorCode . ', please check the logs');
		}
	}
}
