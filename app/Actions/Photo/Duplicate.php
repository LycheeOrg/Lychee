<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo;

use App\Constants\PhotoAlbum as PA;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Duplicate
{
	/**
	 * Duplicates a set of photos.
	 *
	 * @param Collection<int,Photo> $photos the source photos
	 * @param Album                 $album  the destination album; `null` means root album
	 *
	 * @return void
	 */
	public function do(Collection $photos, Album $album): void
	{
		$photos_ids = $photos->pluck('id')->all();
		$insert = array_map(fn (string $id) => ['photo_id' => $id, 'album_id' => $album->id], $photos_ids);

		// Remove existing links.
		DB::table(PA::PHOTO_ALBUM)->whereIn(PA::PHOTO_ID, $photos_ids)->where(PA::ALBUM_ID, '=', $album->id)->delete();
		// Resinsert them (that way we avoid uniqueness errors).
		DB::table(PA::PHOTO_ALBUM)->insert($insert);
	}
}
