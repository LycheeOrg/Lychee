<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\Http\Requests;

use App\Enum\TimelineAlbumGranularity;

interface HasTimelineAlbum
{
	/**
	 * @return TimelineAlbumGranularity|null
	 */
	public function album_timeline(): ?TimelineAlbumGranularity;
}
