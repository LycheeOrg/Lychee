<?php

namespace App\Actions\Album;

class SetPublic extends Action
{
	public function do(string $albumID, array $values): bool
	{
		$album = $this->albumFactory->findModelOrFail($albumID);

		// Convert values
		$album->grants_full_photo = $values['grants_full_photo'];
		$album->is_public = $values['is_public'];
		$album->requires_link = $values['requires_link'];
		$album->is_nsfw = $values['is_nsfw'];
		$album->is_downloadable = $values['is_downloadable'];
		$album->is_share_button_visible = $values['is_share_button_visible'];

		// Set password if provided
		if (array_key_exists('password', $values)) {
			// password is provided => there is a change

			if (isset($values['password'])) {
				// password is not null => we update the value with the hash
				$album->password = bcrypt($values['password']);
			} else {
				// we remove the password
				$album->password = null;
			}
		}

		// Set Public
		if (!$album->save()) {
			return false;
		}

		// Reset permissions for photos
		if ($album->is_public) {
			$album->photos()->update(['is_public' => false]);
		}

		return true;
	}
}
