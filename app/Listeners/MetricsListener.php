<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Listeners;

use App\Events\Metrics\AlbumDownload;
use App\Events\Metrics\AlbumShared;
use App\Events\Metrics\AlbumVisit;
use App\Events\Metrics\PhotoDownload;
use App\Events\Metrics\PhotoFavourite;
use App\Events\Metrics\PhotoShared;
use App\Events\Metrics\PhotoVisit;
use App\Models\Configs;
use App\Models\LiveMetrics;
use Illuminate\Support\Facades\DB;

/**
 * Just logging of Cache events.
 */
class MetricsListener
{
	/**
	 * Handle the event.
	 */
	public function handle(AlbumDownload|AlbumShared|AlbumVisit|PhotoDownload|PhotoFavourite|PhotoShared|PhotoVisit $event): void
	{
		if (Configs::getValueAsBool('metrics_enabled') === false) {
			return;
		}

		// Increment the respective metric in the database
		DB::table($event->table())
			->where('id', '=', $event->id)
			->increment($event->metricAction()->column(), 1);

		DB::table('live_metrics')
			->insert([
				[
					'visitor_id' => $event->visitor_id,
					'action' => $event->metricAction(),
					'album_id' => $event->table() === 'base_albums' ? $event->id : null,
					'photo_id' => $event->table() === 'photos' ? $event->id : null,
					'created_at' => now(),
				]
			]);
	}
}
