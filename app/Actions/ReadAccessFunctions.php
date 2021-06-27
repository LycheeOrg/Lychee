<?php

namespace App\Actions;

use App\Facades\AccessControl;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;

class ReadAccessFunctions
{
	/**
	 * Check if a (public) user has access to an album
	 * if 0 : album does not exist
	 * if 1 : access is granted
	 * if 2 : album is private
	 * if 3 : album is password protected and require user input.
	 *
	 * @param Album $album
	 * @param bool obeyHidden
	 *
	 * @return int
	 */
	public function album($album, bool $obeyHidden = false): int
	{
		if (AccessControl::is_current_user($album->owner_id)) {
			return 1; // access granted
		}

		// Check if the album is shared with us
		if (
			AccessControl::is_logged_in() &&
			$album->shared_with->map(function ($user) {
				return $user->id;
			})->contains(AccessControl::id())
		) {
			return 1; // access granted
		}

		if (
			!$album->is_public() ||
			($obeyHidden && $album->viewable !== 1)
		) {
			return 2;  // Warning: Album private!
		}

		if ($album->password == '') {
			return 1;  // access granted
		}

		if (AccessControl::has_visible_album($album->id)) {
			return 1;  // access granted
		}

		return 3;      // Please enter password first. // Warning: Wrong password!
	}

	/**
	 * Check if a (public) user has access to an album
	 * if 0 : album does not exist
	 * if 1 : access is granted
	 * if 2 : album is private
	 * if 3 : album is password protected and require user input.
	 *
	 * @param int|string $album: Album object or Album id
	 * @param bool obeyHidden
	 *
	 * @return int
	 */
	public function albumID($album, bool $obeyHidden = false): int
	{
		if (in_array($album, [
			'starred',
			'public',
			'recent',
			'unsorted',
		])) {
			if (AccessControl::is_logged_in() && AccessControl::can_upload()) {
				return 1;
			}
			if (($album === 'recent' && Configs::get_value('public_recent', '0') === '1') ||
				($album === 'starred' && Configs::get_value('public_starred', '0') === '1')
			) {
				return 1; // access granted
			} else {
				return 2; // Warning: Album private!
			}
		}

		$album = Album::findOrFail($album);

		return $this->album($album, $obeyHidden);
	}

	/**
	 * Check if a (public) user has access to a picture.
	 *
	 * @param Photo $photo
	 *
	 * @return bool
	 */
	public function photo(Photo $photo): bool
	{
		if (AccessControl::is_current_user($photo->owner_id)) {
			return true;
		}
		if ($photo->public === 1) {
			return true;
		}
		if ($this->albumID($photo->album_id) === 1) {
			return true;
		}

		return false;
	}
}
