<?php

namespace App\Actions\Album;

use App\Exceptions\InvalidPropertyException;
use App\Exceptions\ModelDBException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SetPublic extends Action
{
	/**
	 * @param int                                                                                                                                                         $albumID
	 * @param array{is_public: bool, requires_link: bool, is_nsfw: bool, is_downloadable: bool, is_share_button_visible: bool, grant_full_photo: bool, password: ?string} $shareSettings
	 *
	 * @return void
	 *
	 * @throws ModelNotFoundException
	 * @throws ModelDBException
	 * @throws InvalidPropertyException
	 */
	public function do(int $albumID, array $shareSettings): void
	{
		$album = $this->albumFactory->findModelOrFail($albumID);

		// Convert values
		$album->grants_full_photo = $shareSettings['grants_full_photo'];
		$album->is_public = $shareSettings['is_public'];
		$album->requires_link = $shareSettings['requires_link'];
		$album->is_nsfw = $shareSettings['is_nsfw'];
		$album->is_downloadable = $shareSettings['is_downloadable'];
		$album->is_share_button_visible = $shareSettings['is_share_button_visible'];

		// Set password if provided
		if (array_key_exists('password', $shareSettings)) {
			// password is provided => there is a change
			if ($shareSettings['password'] !== null) {
				// password is not null => we update the value with the hash
				try {
					$album->password = bcrypt($shareSettings['password']);
				} catch (\InvalidArgumentException $e) {
					throw new InvalidPropertyException('Could not hash password', $e);
				}
			} else {
				// we remove the password
				$album->password = null;
			}
		}

		$album->save();

		// Reset permissions for photos
		if ($album->is_public) {
			$album->photos()->update(['photos.is_public' => false]);
		}
	}
}
