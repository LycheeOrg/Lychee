<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Events;

use App\Models\PersonAlbum;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PersonAlbumSaved
{
	use Dispatchable;
	use SerializesModels;

	/**
	 * Create a new event instance.
	 */
	public function __construct(public PersonAlbum $person_album)
	{
	}
}
