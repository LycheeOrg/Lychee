<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Listeners;

use App\Events\AlbumDeleted;
use App\Events\AlbumSaved;
use App\Jobs\RecomputeAlbumStatsJob;
use Illuminate\Support\Facades\Log;

class RecomputeAlbumStatsOnAlbumChange
{
	/**
	 * Handle AlbumSaved event.
	 *
	 * @param AlbumSaved $event
	 *
	 * @return void
	 */
	public function handleAlbumSaved(AlbumSaved $event): void
	{
		// When albums are saved, recompute their stats
		foreach ($event->album_ids as $album_id) {
			Log::info("Album {$album_id} saved, dispatching recompute job");
			RecomputeAlbumStatsJob::dispatch($album_id);
		}
	}

	/**
	 * Handle AlbumDeleted event.
	 *
	 * @param AlbumDeleted $event
	 *
	 * @return void
	 */
	public function handleAlbumDeleted(AlbumDeleted $event): void
	{
		// Dispatch job only if album had a parent
		RecomputeAlbumStatsJob::dispatchIf(
			$event->parent_id !== null,
			$event->parent_id
		);

		if ($event->parent_id !== null) {
			Log::info("Album deleted from parent {$event->parent_id}, dispatching recompute job");
		}
	}
}
