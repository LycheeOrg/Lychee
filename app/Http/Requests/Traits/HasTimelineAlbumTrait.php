<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use App\Enum\TimelineAlbumGranularity;

trait HasTimelineAlbumTrait
{
	protected ?TimelineAlbumGranularity $album_timeline = null;

	/**
	 * @return TimelineAlbumGranularity|null
	 */
	public function album_timeline(): ?TimelineAlbumGranularity
	{
		return $this->album_timeline;
	}
}
