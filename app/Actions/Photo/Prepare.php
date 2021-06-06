<?php

namespace App\Actions\Photo;

use App\Facades\AccessControl;
use App\Models\Configs;
use App\Models\Photo;

class Prepare extends SymLinker
{
	/**
	 * @param Photo $photo
	 *
	 * @return array
	 */
	public function do(Photo $photo): array
	{
		$return = $photo->toReturnArray();

		$this->symLinkFunctions->getUrl($photo, $return);

		//! This can probably be refactored
		if (!AccessControl::is_current_user($photo->owner_id)) {
			if ($photo->album_id != null) {
				$album = $photo->album;
				if (!$album->is_full_photo_visible()) {
					$photo->downgrade($return);
				}

				// if 2 : picture is public by album being public (if being in an album).
				if ($album->is_public()) {
					$return['public'] = '2';
				}

				$return['downloadable'] = $album->is_downloadable() ? '1' : '0';
				$return['share_button_visible'] = $album->is_share_button_visible() ? '1' : '0';
			} else { // Unsorted
				if (Configs::get_value('full_photo', '1') != '1') {
					$photo->downgrade($return);
				}
				$return['downloadable'] = Configs::get_value('downloadable', '0');
				$return['share_button_visible'] = Configs::get_value('share_button_visible', '0');
			}
		} else {
			if ($photo->album_id != null && $photo->album->is_public()) {
				$return['public'] = '2';
			}
			$return['downloadable'] = '1';
			$return['share_button_visible'] = '1';
		}

		$return['license'] = $photo->get_license();

		return $return;
	}
}
