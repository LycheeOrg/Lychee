<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched once an async recompute job (stats or size) has finished
 * writing its computed fields for an album.
 *
 * Consumed **only** by this feature's cache-invalidation listener — never
 * by `RecomputeAlbumSizeOnAlbumChange`/`RecomputeAlbumStatsOnAlbumChange`,
 * to avoid a dispatch loop.
 */
class AlbumComputedDataUpdated
{
	use Dispatchable;
	use SerializesModels;

	/**
	 * Create a new event instance.
	 *
	 * @param string $album_id The ID of the album whose computed data was updated
	 */
	public function __construct(public string $album_id)
	{
	}
}
