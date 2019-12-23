<?php

namespace App\ControllerFunctions;

use App\Album;
use App\Configs;
use App\ModelFunctions\SessionFunctions;
use App\Photo;
use App\User;

class ReadAccessFunctions
{
	/**
	 * @var SessionFunctions
	 */
	private $sessionFunctions;

	/**
	 * @param SessionFunctions $sessionFunctions
	 */
	public function __construct(SessionFunctions $sessionFunctions)
	{
		$this->sessionFunctions = $sessionFunctions;
	}

	/**
	 * Check if a (public) user has access to an album
	 * if 0 : album does not exists
	 * if 1 : access is granted
	 * if 2 : album is private
	 * if 3 : album is password protected and require user input.
	 *
	 * @param mixed $album: Album object or Album id
	 * @param bool obeyHidden
	 *
	 * @return int
	 */
	public function album($album, bool $obeyHidden = false)
	{
		if (!is_object($album)) {
			if (in_array($album, [
				'f',
				's',
				'r',
				'0',
			])) {
				if ($this->sessionFunctions->is_logged_in()) {
					$id = $this->sessionFunctions->id();

					$user = User::find($id);
					if ($id == 0 || $user->upload) {
						return 1; // access granted
					}
				}

				if (($album === 'r' && Configs::get_value('public_recent', '0') === '1') ||
					($album === 'f' && Configs::get_value('public_starred', '0') === '1')) {
					return 1; // access granted
				} else {
					return 2; // Warning: Album private!
				}
			} else {
				$album = Album::find($album);
				if ($album == null) {
					return 0;  // Does not exist
				}
			}
		}

		if ($this->sessionFunctions->is_current_user($album->owner_id)) {
			return 1; // access granted
		}

		// Check if the album is shared with us
		if ($this->sessionFunctions->is_logged_in() &&
			$album->shared_with->map(function ($user) {
				return $user->id;
			})->contains($this->sessionFunctions->id())) {
			return 1; // access granted
		}

		if ($album->public != 1 ||
			($obeyHidden && $album->visible_hidden !== 1)) {
			return 2;  // Warning: Album private!
		}

		if ($album->password == '') {
			return 1;  // access granted
		}

		if ($this->sessionFunctions->has_visible_album($album->id)) {
			return 1;  // access granted
		}

		return 3;      // Please enter password first. // Warning: Wrong password!
	}

	/**
	 * Check if a (public) user has access to a picture.
	 *
	 * @param Photo $photo
	 *
	 * @return bool
	 */
	public function photo(Photo $photo)
	{
		if ($this->sessionFunctions->is_current_user($photo->owner_id)) {
			return true;
		}
		if ($photo->public === 1) {
			return true;
		}
		if ($this->album($photo->album_id) === 1) {
			return true;
		}

		return false;
	}
}
