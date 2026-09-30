<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Album\StructOfArrays\Traits;

use App\Actions\Album\StructOfArrays\SideCoverIds;
use App\Http\Controllers\Gallery\AlbumListController;
use App\Http\Resources\V3\AlbumCategoryResource;
use App\Models\AlbumUserThumb;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Repositories\ConfigManager;
use Illuminate\Support\Collection;

/**
 * Builds the struct-of-arrays {@see AlbumCategoryResource} shared by the
 * flat, un-bucketed category listings (`/Albums/tags`, `/Albums/pinned`).
 * `$resolve_cover` is opt-in: a `TagAlbum` row's `cover_id` is already the
 * final answer, but a real `Album` row (pinned) needs
 * {@see AlbumListController::resolveCoverId()}'s owner/viewer-aware
 * auto-cover fallback.
 */
trait BuildsAlbumCategoryResource
{
	/**
	 * @param Collection<int,object{id:string,title:string,cover_id:?string,owner_id:int,auto_cover_id_max_privilege?:?string,auto_cover_id_least_privilege?:?string,password?:?string}> $rows
	 */
	private function toCategoryResource(Collection $rows, bool $resolve_cover = false, ?User $user = null): AlbumCategoryResource
	{
		// Tag rows have no auto-cover columns; their primary (when no manual
		// cover is set) and side covers come from the viewer's cache rows,
		// read once for the whole listing (Feature 075, FR-075-09).
		$cache_rows = $resolve_cover ? new Collection() : AlbumUserThumb::rowsForViewer($rows->pluck('id')->all());
		$ids = [];
		$titles = [];
		$cover_ids = [];
		$cover_ids_2 = [];
		$cover_ids_3 = [];
		$owner_ids = [];
		$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();
		$side_covers_enabled = resolve(ConfigManager::class)->getValueAsBool('album_hover_side_covers_enabled');
		foreach ($rows as $row) {
			$ids[] = $row->id;
			$titles[] = $row->title;
			$cache_row = $cache_rows->get($row->id);
			$raw_cover_id = $resolve_cover ? AlbumListController::rawCoverId($row, $user) : ($row->cover_id ?? $cache_row?->photo_id);
			$cover_id = AlbumListController::applyLockedCoverGate($raw_cover_id, $row, $unlocked_album_ids);
			// Side covers (Feature 075): rows carrying the auto-cover triples
			// (pinned regular albums) resolve them from the row; tag rows
			// from the viewer cache, hidden while the album is locked.
			[$cover_id_2, $cover_id_3] = $resolve_cover
				? SideCoverIds::forAlbumRow($row, $cover_id, $user, $unlocked_album_ids, $side_covers_enabled)
				: SideCoverIds::fromCacheRow($cache_row, $cover_id, $side_covers_enabled && !SideCoverIds::isHiddenByLock($row, $unlocked_album_ids));
			$cover_ids[] = $cover_id;
			$cover_ids_2[] = $cover_id_2;
			$cover_ids_3[] = $cover_id_3;
			$owner_ids[] = (string) $row->owner_id;
		}

		return new AlbumCategoryResource(ids: $ids, titles: $titles, cover_ids: $cover_ids, cover_ids_2: $cover_ids_2, cover_ids_3: $cover_ids_3, owner_ids: $owner_ids);
	}
}
