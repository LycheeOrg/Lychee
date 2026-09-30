<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Album\StructOfArrays;

use App\Repositories\ConfigManager;

/**
 * Feature 075 (FR-075-05, FR-075-09): the two side cover ids shown behind
 * an album's cover on hover, derived from ranks 1–3 of the automatic
 * cover selection already stored on the row.
 *
 * Pure: the only I/O is one config read for the locked-album rule.
 * Regular albums read the viewer's automatic cover triple that
 * {@see JoinAutoCover} joined onto the listing row ({@see self::forAlbumRow()},
 * Feature 076); tag/person/smart albums read the viewer's cached
 * `album_user_thumbs` row ({@see self::fromCacheRow()}).
 *
 * A side is never the primary cover and never `null` before a non-null
 * one, so the tile can render `[side_1 ?? cover, side_2 ?? cover]`.
 */
final class SideCoverIds
{
	/**
	 * Side covers for a regular album listing row.
	 *
	 * @param object            $row                the `toBase()` row, carrying `id`, `password` and the `auto_cover_id`, `auto_cover_id_2`, `auto_cover_id_3` aliases of {@see JoinAutoCover}
	 * @param string|null       $primary            the already-resolved primary cover ({@see \App\Http\Controllers\Gallery\AlbumListController::resolveCoverId()})
	 * @param array<int,string> $unlocked_album_ids {@see \App\Policies\AlbumPolicy::getUnlockedAlbumIDs()}
	 * @param bool              $enabled            `album_hover_side_covers_enabled`
	 *
	 * @return array{0:string|null,1:string|null}
	 */
	public static function forAlbumRow(object $row, ?string $primary, array $unlocked_album_ids, bool $enabled): array
	{
		if (!$enabled || $primary === null || self::isHiddenByLock($row, $unlocked_album_ids)) {
			return [null, null];
		}

		return self::pick([$row->auto_cover_id, $row->auto_cover_id_2, $row->auto_cover_id_3], $primary);
	}

	/**
	 * Side covers from the viewer's cached cover row of a tag/person/smart album.
	 *
	 * @param object|null $cache_row the viewer's `album_user_thumbs` row (`photo_id`, `photo_id_2`, `photo_id_3`), `null` when none exists yet
	 * @param string|null $primary   the already-resolved primary cover
	 * @param bool        $enabled   `album_hover_side_covers_enabled`, already combined with {@see self::isHiddenByLock()} by the caller when the album can be locked
	 *
	 * @return array{0:string|null,1:string|null}
	 */
	public static function fromCacheRow(?object $cache_row, ?string $primary, bool $enabled): array
	{
		if (!$enabled || $primary === null || $cache_row === null) {
			return [null, null];
		}

		return self::pick([$cache_row->photo_id, $cache_row->photo_id_2, $cache_row->photo_id_3], $primary);
	}

	/**
	 * Whether side covers must be hidden because the album is password-locked
	 * for this viewer. Only `show_cover_of_locked_albums` reveals them;
	 * `show_selected_cover_on_locked_albums` does not, since sides are always
	 * automatically picked (FR-075-05 rule 4).
	 *
	 * @param object            $row                carries `id` and `password`
	 * @param array<int,string> $unlocked_album_ids
	 */
	public static function isHiddenByLock(object $row, array $unlocked_album_ids): bool
	{
		$is_locked = ($row->password ?? null) !== null && !in_array($row->id, $unlocked_album_ids, true);

		return $is_locked && !resolve(ConfigManager::class)->getValueAsBool('show_cover_of_locked_albums');
	}

	/**
	 * Drop `null`s and the primary, keep order, take the first two.
	 *
	 * @param array<int,string|null> $ids
	 *
	 * @return array{0:string|null,1:string|null}
	 */
	private static function pick(array $ids, string $primary): array
	{
		$candidates = array_values(array_filter($ids, fn (?string $id): bool => $id !== null && $id !== $primary));

		return [$candidates[0] ?? null, $candidates[1] ?? null];
	}
}
