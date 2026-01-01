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
use App\Jobs\RecomputeAlbumStatsJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecomputeAlbumStatsOnPhotoChange
{
	/**
	 * Handle PhotoSaved event.
	 *
	 * @param PhotoSaved $event
	 *
	 * @return void
	 */
	public function handlePhotoSaved(PhotoSaved $event): void
	{
		// Get all albums this photo belongs to
		$album_ids = DB::table(PA::PHOTO_ALBUM)
			->where('photo_id', '=', $event->photo->id)
			->pluck('album_id')
			->all();

		if (count($album_ids) === 0) {
			// Photo not in any album, nothing to recompute
			return;
		}

		Log::info("Photo {$event->photo->id} saved, dispatching recompute jobs for " . count($album_ids) . ' album(s)');
		foreach ($album_ids as $album_id) {
			RecomputeAlbumStatsJob::dispatch($album_id);
		}
	}

	/**
	 * Handle PhotoDeleted event.
	 *
	 * @param PhotoDeleted $event
	 *
	 * @return void
	 */
	public function handlePhotoDeleted(PhotoDeleted $event): void
	{
		Log::info("Photo deleted from album {$event->album_id}, dispatching recompute job");
		RecomputeAlbumStatsJob::dispatch($event->album_id);
	}
}
