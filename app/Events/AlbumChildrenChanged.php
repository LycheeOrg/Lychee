<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AlbumChildrenChanged
{
	use Dispatchable;
	use SerializesModels;

	/**
	 * Create a new event instance.
	 *
	 * @param array<int,string|null> $parent_ids IDs of the parent albums whose set of direct children changed (`null` for root), batched so listeners can act once per call instead of once per parent
	 */
	public function __construct(public array $parent_ids)
	{
	}
}
