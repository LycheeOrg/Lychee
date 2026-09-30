<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Album;

use App\Models\AlbumUserThumb;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Feature 076 (FR-076-07, ADR-076-01): picks a regular album's automatic
 * cover row out of its precomputed `album_user_thumbs` rows.
 *
 * A regular album has an owner row (`user_id = owner_id`, max privilege)
 * and at most one least-privilege row, keyed on the single user it is
 * shared with or on `NULL`. Since those are the only keys, "the viewer's
 * own row, else the `NULL` row" is right for every non-admin viewer;
 * admins read the owner row.
 *
 * Pure: no query, no config read.
 */
final class AutoCoverRows
{
	/**
	 * @param Collection<int,AlbumUserThumb> $rows     precomputed rows of one album
	 * @param int                            $owner_id the album's owner
	 * @param User|null                      $user     the viewer
	 */
	public static function forViewer(Collection $rows, int $owner_id, ?User $user): ?AlbumUserThumb
	{
		$key = $user?->may_administrate === true ? $owner_id : $user?->id;

		return self::keyed($rows, $key) ?? self::publicRow($rows);
	}

	/**
	 * The least-privilege row of a publicly shared album, if any.
	 *
	 * @param Collection<int,AlbumUserThumb> $rows
	 */
	public static function publicRow(Collection $rows): ?AlbumUserThumb
	{
		return self::keyed($rows, null);
	}

	/**
	 * @param Collection<int,AlbumUserThumb> $rows
	 */
	private static function keyed(Collection $rows, ?int $user_id): ?AlbumUserThumb
	{
		return $rows->first(fn (AlbumUserThumb $row): bool => $row->user_id === $user_id);
	}
}
