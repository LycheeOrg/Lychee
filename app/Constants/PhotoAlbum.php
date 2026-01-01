<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Constants;

use Illuminate\Database\Query\JoinClause;

class PhotoAlbum
{
	// computed table name
	public const PHOTO_ALBUM = 'photo_album';

	// Id names
	public const PHOTO_ID = 'photo_album.photo_id';
	public const ALBUM_ID = 'photo_album.album_id';

	public static function isJoinedToPhoto(JoinClause $join): bool
	{
		if ($join->table !== self::PHOTO_ALBUM) {
			return false;
		}
		// We now need to check if the join column is correct.

		/** @var array{type:string,first:string,operator:string,second:string}[] */
		$wheres = $join->wheres ?? [];
		foreach ($wheres as $where) {
			if (str_contains($where['first'], 'photo_id') || str_contains($where['second'], 'photo_id')) {
				return true;
			}
		}

		// Likely to not be the join we want (e.g. on the table).
		return false;
	}
}