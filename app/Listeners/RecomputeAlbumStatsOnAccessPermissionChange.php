<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Listeners;

use App\Events\AccessPermissionChanged;
use App\Jobs\RecomputeAlbumStatsJob;

/**
 * Feature 076 (FR-076-13, ADR-076-01): an album's least-privilege cover row
 * is keyed on its permission set (the single shared user, or `NULL`), and
 * its ancestors' least-privilege covers depend on which of its photos are
 * searchable. A change of its access permissions therefore recomputes it,
 * propagating to the parents like any photo or album change.
 *
 * Listens to the explicitly dispatched {@see AccessPermissionChanged}
 * domain event, never to Eloquent model events.
 */
class RecomputeAlbumStatsOnAccessPermissionChange
{
	public function handle(AccessPermissionChanged $event): void
	{
		RecomputeAlbumStatsJob::dispatch($event->base_album_id);
	}
}
