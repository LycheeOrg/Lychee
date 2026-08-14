<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccessPermissionChanged
{
	use Dispatchable;
	use SerializesModels;

	/**
	 * Create a new event instance.
	 *
	 * @param string $base_album_id The ID of the album whose access permissions changed
	 */
	public function __construct(public string $base_album_id)
	{
	}
}
