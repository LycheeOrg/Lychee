<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AlbumRouteCacheUpdated
{
	use Dispatchable;
	use InteractsWithSockets;
	use SerializesModels;

	/**
	 * This event is fired when the gallery is updated.
	 * Note that:
	 * - if $album_id is null, then all routes are to be cleared.
	 * - if $album_id is '', then only the root is updated.
	 * - if $album_id is an id, then only that id is updated.
	 *
	 * @param string|null $album_id
	 *
	 * @return void
	 */
	public function __construct(public ?string $album_id = null)
	{
	}
}
