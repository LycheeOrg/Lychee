<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Events;

use App\Models\Photo;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PhotoSaved
{
	use Dispatchable;
	use SerializesModels;

	/**
	 * Create a new event instance.
	 */
	public function __construct(public Photo $photo)
	{
	}
}
