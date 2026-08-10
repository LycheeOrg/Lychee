<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AlbumSaved
{
	use Dispatchable;
	use SerializesModels;

	/** @var array<int,string> */
	public array $parent_ids;

	/**
	 * Create a new event instance.
	 *
	 * @param array<int,string>      $album_ids  IDs of the saved albums, batched so listeners can act once per call instead of once per album
	 * @param array<int,string|null> $parent_ids IDs of the parent albums affected by the save; `null` (root) entries are dropped, since listeners already forget the root listing unconditionally on every save
	 */
	public function __construct(public array $album_ids, array $parent_ids)
	{
		// @phpstan-ignore arrayFilter.strict (We ignore this one voluntarily)
		$this->parent_ids = array_values(array_filter($parent_ids));
	}
}
