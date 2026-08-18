<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PhotoPersonsChanged
{
	use Dispatchable;
	use SerializesModels;

	/**
	 * Create a new event instance.
	 *
	 * @param array<int,string> $photo_ids  batched so listeners can resolve affected albums once per call instead of once per photo
	 * @param array<int,string> $person_ids the union of old and new person IDs affected by the change (both sides of a
	 *                                      reassignment/removal), so listeners which cache by person ID (e.g. the
	 *                                      PersonAlbum "matching albums" cache) can evict precisely. Deriving this
	 *                                      from current `faces` table state after the fact would miss a person who
	 *                                      no longer has any face on these photos.
	 */
	public function __construct(public array $photo_ids, public array $person_ids)
	{
	}
}
