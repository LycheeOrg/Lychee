<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Album\StructOfArrays\Traits;

use App\Http\Controllers\Gallery\AlbumListController;
use App\Http\Resources\V3\AlbumCategoryResource;
use App\Models\User;
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
	 * @param Collection<int,object{id:string,title:string,cover_id:?string,owner_id:int,auto_cover_id_max_privilege?:?string,auto_cover_id_least_privilege?:?string}> $rows
	 */
	private function toCategoryResource(Collection $rows, bool $resolve_cover = false, ?User $user = null): AlbumCategoryResource
	{
		$ids = [];
		$titles = [];
		$cover_ids = [];
		$owner_ids = [];
		foreach ($rows as $row) {
			$ids[] = $row->id;
			$titles[] = $row->title;
			$cover_ids[] = $resolve_cover ? AlbumListController::resolveCoverId($row, $user) : $row->cover_id;
			$owner_ids[] = (string) $row->owner_id;
		}

		return new AlbumCategoryResource(ids: $ids, titles: $titles, cover_ids: $cover_ids, owner_ids: $owner_ids);
	}
}
