<?php

namespace App\Actions;

use App\Facades\AccessControl;
use App\Models\Photo;

// TODO: Converts this class into a `PhotoAuthorizationProvider` in the same spirit as `AlbumAuthorizationProvider`.
class ReadAccessFunctions
{
	protected AlbumAuthorisationProvider $albumAuthorisationProvider;

	public function __construct()
	{
		$this->albumAuthorisationProvider = resolve(AlbumAuthorisationProvider::class);
	}

	/**
	 * Check if the current user has access to a picture.
	 *
	 * TODO: Move this method into a `PhotoAuthorizationProvider` in the same spirit as `AlbumAuthorizationProvider`.
	 *
	 * @param Photo $photo
	 *
	 * @return bool
	 */
	public function photo(Photo $photo): bool
	{
		return
			AccessControl::is_current_user($photo->owner_id) ||
			$photo->public === 1 ||
			$this->albumAuthorisationProvider->isAccessible($photo->album_id);
	}
}
