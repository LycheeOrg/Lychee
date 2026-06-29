<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\Actions\Album\Delete;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Deletes PersonAlbums that have zero persons remaining
 * (e.g. after a Person deletion).
 */
class CleanupOrphanedPersonAlbumsJob implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	public function handle(): void
	{
		$orphaned_ids = DB::table('person_albums')
			->leftJoin('person_albums_persons', 'person_albums.id', '=', 'person_albums_persons.album_id')
			->whereNull('person_albums_persons.id')
			->pluck('person_albums.id')
			->all();

		if (count($orphaned_ids) === 0) {
			return;
		}

		Log::info('CleanupOrphanedPersonAlbumsJob: deleting ' . count($orphaned_ids) . ' orphaned person album(s).');

		$delete = resolve(Delete::class);
		$delete->do($orphaned_ids);
	}
}
