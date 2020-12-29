<?php

namespace App\Actions\Album\Extensions;

use App\Assets\Helpers;
use App\Models\Album;
use App\Models\Logs;
use App\Response;
use Illuminate\Database\QueryException;

trait StoreAlbum
{
	/**
	 * @return Album|Response
	 */
	public function store_album(Album &$album)
	{
		do {
			$retry = false;

			try {
				if (!$album->save()) {
					return Response::error('Could not save album in database!');
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

					return Response::error('Something went wrong, error' . $errorCode . ', please check the logs');
				}
			}
		} while ($retry);

		return $album;
	}
}
