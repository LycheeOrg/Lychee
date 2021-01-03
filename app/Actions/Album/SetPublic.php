<?php

namespace App\Actions\Album;

use App\Models\Logs;

class SetPublic extends Action
{
	public function do(string $albumID, array $values): bool
	{
		if ($this->albumFactory->is_smart($albumID)) {
			Logs::error(__METHOD__, __LINE__, 'Not applicable to smart albums.');

			return false;
		}

		$album = $this->albumFactory->make($albumID);

		// Convert values
		$album->full_photo = ($values['full_photo'] === '1' ? 1 : 0);
		$album->public = ($values['public'] === '1' ? 1 : 0);
		$album->viewable = ($values['visible'] === '1' ? 1 : 0);
		$album->nsfw = ($values['nsfw'] === '1' ? 1 : 0);
		$album->downloadable = ($values['downloadable'] === '1' ? 1 : 0);
		$album->share_button_visible = ($values['share_button_visible'] === '1' ? 1 : 0);

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
		if ($album->public == 1) {
			$album->photos()->update(['public' => '0']);
		}

		return true;
	}
}
