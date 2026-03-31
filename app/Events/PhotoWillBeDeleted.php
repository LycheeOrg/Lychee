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
 * Fired just before a photo is hard-deleted from the database.
 * Carries a full photo snapshot so listeners can access the data
 * even after deletion completes.
 *
 * Dispatched from the Delete action before executeDelete().
 * Do NOT use Eloquent model observers or deleting hooks — use this event instead.
 */
class PhotoWillBeDeleted
{
	use Dispatchable;
	use SerializesModels;

	/**
	 * Create a new event instance.
	 *
	 * @param string                                   $photo_id      the ID of the photo being deleted
	 * @param string                                   $album_id      the ID of the album the photo is being removed from
	 * @param string                                   $title         the title of the photo
	 * @param array<int,array{type:string,url:string}> $size_variants array of size variant snapshots with 'type' (lowercase name) and 'url'
	 */
	public function __construct(
		public string $photo_id,
		public string $album_id,
		public string $title,
		public array $size_variants,
	) {
	}
}
