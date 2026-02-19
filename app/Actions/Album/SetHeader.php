<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Album;

use App\Http\Controllers\Gallery\AlbumController;
use App\Models\Album;
use App\Models\Photo;

class SetHeader
{
	/**
	 * Set the header image of the album.
	 *
	 * @param Album  $album
	 * @param bool   $is_compact
	 * @param ?Photo $photo
	 * @param bool   $shall_override
	 *
	 * @return Album
	 */
	public function do(Album $album, bool $is_compact, ?Photo $photo, bool $shall_override = false): Album
	{
		if ($is_compact) {
			$album->header_id = AlbumController::COMPACT_HEADER;
			$album->header_photo_focus = null;
		} else {
			$old_header_id = $album->header_id;
			$album->header_id = ($album->header_id !== $photo?->id || $shall_override) ? $photo?->id : null;

			if ($old_header_id !== $album->header_id) {
				$album->header_photo_focus = null;
			}
		}
		$album->save();

		return $album;
	}
}
