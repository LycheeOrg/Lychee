<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Listeners;

use App\Events\PhotoDeleted;
use App\Events\PhotoSaved;
use App\Jobs\RecomputeAlbumSizeJob;
use App\Models\Photo;
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
	 * When a photo is saved, it may affect one or more albums:
	 * - Photo uploaded to album: affects target album
	 * - Photo moved between albums: affects both source and target albums
	 *
	 * We dispatch a job for each album the photo belongs to.
	 *
	 * @param PhotoSaved $event
	 *
	 * @return void
	 */
	public function handlePhotoSaved(PhotoSaved $event): void
	{
		$photo = Photo::findOrFail($event->photo_id);

		// Get all albums this photo belongs to (many-to-many relationship)
		$album_ids = $photo->albums()->pluck('id');

		foreach ($album_ids as $album_id) {
			Log::debug("Photo {$event->photo_id} saved, dispatching size recompute for album {$album_id}");
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
