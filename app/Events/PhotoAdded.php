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
 * Fired when a photo is added to Lychee (upload, import, or duplication).
 * Dispatched from SetParent pipe for newly created photo records only.
 */
class PhotoAdded
{
	use Dispatchable;
	use SerializesModels;

	/**
	 * Create a new event instance.
	 */
	public function __construct(public string $photo_id)
	{
	}
}
