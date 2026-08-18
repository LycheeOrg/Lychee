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
		if (count($event->photo_ids) === 0) {
			return;
		}

		// Get all albums these photos belong to
		$album_ids = DB::table(PA::PHOTO_ALBUM)
			->whereIn('photo_id', $event->photo_ids)
			->distinct()
			->pluck('album_id')
			->all();

		if (count($album_ids) === 0) {
			// Photos not in any album, nothing to recompute
			return;
		}

		Log::info(count($event->photo_ids) . ' photo(s) saved, dispatching recompute jobs for ' . count($album_ids) . ' album(s)');
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
