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
 * Fired when a photo is moved from one album to another.
 * Dispatched from MoveOrDuplicate action when source and destination albums differ.
 */
class PhotoMoved
{
	use Dispatchable;
	use SerializesModels;

	/**
	 * Create a new event instance.
	 */
	public function __construct(
		public string $photo_id,
		public string $from_album_id,
		public string $to_album_id,
	) {
	}
}
