<?php

namespace App\Actions\Album;

use App\Http\Controllers\Gallery\AlbumController;
use App\Models\Album;
use App\Models\Photo;

class SetHeader extends Action
{
	/**
	 * Set the header image of the album.
	 *
	 * @param Album  $album
	 * @param bool   $is_compact
	 * @param ?Photo $photo
	 *
	 * @return Album
	 */
	public function do(Album $album, bool $is_compact, ?Photo $photo): Album
	{
		if ($is_compact) {
			$album->header_id = AlbumController::COMPACT_HEADER;
		} else {
			$album->header_id = ($album->header_id === $photo?->id) ? null : $photo?->id;
		}
		$album->save();

		return $album;
	}
}
