<?php

namespace App\Actions\Album;

use App\Models\Album;

class SetPublic
{
	public function do(Album $album, array $values): bool
	{
		// Convert values
		$album->full_photo = ($values['full_photo'] === '1' ? 1 : 0);
		$album->public = ($values['public'] === '1' ? 1 : 0);
		$album->viewable = ($values['visible'] === '1' ? 1 : 0);
		$album->nsfw = ($values['nsfw'] === '1' ? 1 : 0);
		$album->downloadable = ($values['downloadable'] === '1' ? 1 : 0);
		$album->share_button_visible = ($values['share_button_visible'] === '1' ? 1 : 0);

		// Set public
		if (!$album->save()) {
			return false;
		}

		// Reset permissions for photos
		if ($album->public == 1) {
			$album->photos()->update(['public' => '0']);
		}

		if (isset($values['password'])) {
			if (strlen($values['password']) > 0) {
				$album->password = bcrypt($values['password']);
			} else {
				$album->password = null;
			}
			if (!$album->save()) {
				return false;
			}
		}

		return true;
	}
}
