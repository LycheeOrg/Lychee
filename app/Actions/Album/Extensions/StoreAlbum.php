<?php

namespace App\Actions\Album\Extensions;

use App\Exceptions\JsonError;
use App\Facades\Helpers;
use App\Models\Album;
use App\Models\Logs;
use Illuminate\Database\QueryException;

trait StoreAlbum
{
	/**
	 * @return Album
	 */
	public function store_album(Album &$album)
	{
		do {
			$retry = false;

			try {
				if (!$album->save()) {
					throw new JsonError('Could not save album in database!');
				}
			} catch (QueryException $e) {
				$errorCode = $e->getCode();
				if ($errorCode == 23000 || $errorCode == 23505) {
					// Duplicate entry
					do {
						usleep(rand(0, 1000000));
						$newId = Helpers::generateID();
					} while ($newId === $album->id);

					$album->id = $newId;
					$retry = true;
				} else {
					Logs::error(__METHOD__, __LINE__, 'Something went wrong, error ' . $errorCode . ', ' . $e->getMessage());

					throw new JsonError('Something went wrong, error' . $errorCode . ', please check the logs');
				}
			}
		} while ($retry);

		return $album;
	}
}
