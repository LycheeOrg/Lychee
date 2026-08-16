<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Listeners;

use App\Constants\PhotoAlbum as PA;
use App\Events\PhotoDeleted;
use App\Events\PhotoSaved;
use App\Jobs\RecomputeAlbumSizeJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Listener that triggers album size statistics recomputation when photos are mutated.
 *
 * Handles:
 * - PhotoSaved: Covers photo creation, updates, and moves between albums
 * - PhotoDeleted: Covers photo deletion
 *
 * For each photo mutation, dispatches RecomputeAlbumSizeJob for all affected albums.
 */
class RecomputeAlbumSizeOnPhotoMutation
{
	/**
	 * Handle PhotoSaved event (creation, update, move).
	 *
	 * When photos are saved, they may affect one or more albums:
	 * - Photo uploaded to album: affects target album
	 * - Photo moved between albums: affects both source and target albums
	 *
	 * We dispatch a job for each distinct album the photos belong to.
	 *
	 * @param PhotoSaved $event
	 *
	 * @return void
	 */
	public function handlePhotoSaved(PhotoSaved $event): void
	{
		if (count($event->photo_ids) === 0) {
			return;
		}

		// Get all albums these photos belong to (many-to-many relationship)
		$album_ids = DB::table(PA::PHOTO_ALBUM)
			->whereIn('photo_id', $event->photo_ids)
			->distinct()
			->pluck('album_id');

		foreach ($album_ids as $album_id) {
			Log::debug("Dispatching size recompute for album {$album_id} after photo(s) saved");
			RecomputeAlbumSizeJob::dispatch($album_id);
		}
	}

	/**
	 * Handle PhotoDeleted event.
	 *
	 * When a photo is deleted, the event contains the album_id that the photo belonged to.
	 * Dispatch a recomputation job for that album.
	 *
	 * @param PhotoDeleted $event
	 *
	 * @return void
	 */
	public function handlePhotoDeleted(PhotoDeleted $event): void
	{
		$album_id = $event->album_id;

		Log::debug("Photo deleted from album {$album_id}, dispatching size recompute");
		RecomputeAlbumSizeJob::dispatch($album_id);
	}
}
