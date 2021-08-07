<?php

namespace App\Actions\Album;

use App\Facades\AccessControl;
use App\Models\Album;
use Illuminate\Support\Facades\Hash;

class Unlock extends Action
{
	/**
	 * Provided a password and an album, check if the album can be
	 * unlocked. If yes, unlock all albums with the same password.
	 *
	 * @param string $albumID
	 *
	 * @return array
	 */
	public function do(?string $albumID, $password): bool
	{
		if ($this->albumFactory->isBuiltInSmartAlbum($albumID)) {
			return false;
		}

		$album = $this->albumFactory->findOrFail($albumID);
		if ($album->public) {
			if ($album->password === '') {
				return true;
			}
			if (AccessControl::has_visible_album($album->id)) {
				return true;
			}
			$password ??= '';
			if (Hash::check($password, $album->password)) {
				$this->propagate($password);

				return true;
			}
		}

		return false;
	}

	/**
	 * Provided a password, add all the albums that the password unlocks.
	 */
	public function propagate(string $password): void
	{
		// We add all the albums that the password unlocks so that the
		// user is not repeatedly asked to enter the password as they
		// browse through the hierarchy.  This should be safe as the
		// list of such albums is not exposed to the user and is
		// considered as the last access check criteria.
		$albums = Album::whereNotNull('password')->where('password', '!=', '')->get();
		$albumIDs = [];
		foreach ($albums as $album) {
			if (Hash::check($password, $album->password)) {
				$albumIDs[] = $album->id;
			}
		}

		AccessControl::add_visible_albums($albumIDs);
	}
}
