<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TagAlbumSaved
{
	use Dispatchable;
	use SerializesModels;

	/**
	 * Create a new event instance.
	 *
	 * @param array<int,string> $tag_album_ids IDs of the saved tag albums, batched so listeners can act once per call instead of once per tag album
	 */
	public function __construct(public array $tag_album_ids)
	{
	}
}
