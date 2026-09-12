<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PhotoTagsChanged
{
	use Dispatchable;
	use SerializesModels;

	/**
	 * Create a new event instance.
	 *
	 * @param array<int,string> $photo_ids batched so listeners can resolve affected albums once per call instead of once per photo
	 * @param array<int,int>    $tag_ids   the union of old and new tag IDs affected by the change (both sides of an
	 *                                     attach/detach), so listeners which cache by tag ID (e.g. the TagAlbum
	 *                                     photo-listing cache) can evict precisely. Deriving this from current
	 *                                     `photos_tags` state after the fact would miss a tag that was just removed
	 *                                     — mirrors {@see PhotoPersonsChanged::$person_ids} exactly.
	 */
	public function __construct(public array $photo_ids, public array $tag_ids)
	{
	}
}
