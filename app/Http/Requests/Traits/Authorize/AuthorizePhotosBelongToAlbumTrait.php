<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Traits\Authorize;

use App\Constants\PhotoAlbum as PA;
use App\Models\Photo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Binds an `album_id` used for authorization to the objects selected by ID.
 *
 * Without that binding, permission obtained on an album the caller owns can be
 * spent on photos living in somebody else's album (GHSA-x6f7-qp5q-w37f).
 */
trait AuthorizePhotosBelongToAlbumTrait
{
	/**
	 * Check that every given photo is linked to the given album.
	 *
	 * @param Collection<int,Photo> $photos
	 * @param string                $album_id
	 *
	 * @return bool
	 */
	protected function allPhotosBelongToAlbum(Collection $photos, string $album_id): bool
	{
		$photo_ids = $photos->pluck('id')->unique()->values();

		$linked_count = DB::table(PA::PHOTO_ALBUM)
			->where(PA::ALBUM_ID, '=', $album_id)
			->whereIn(PA::PHOTO_ID, $photo_ids->all())
			->distinct()
			->count(PA::PHOTO_ID);

		return $linked_count === $photo_ids->count();
	}
}
