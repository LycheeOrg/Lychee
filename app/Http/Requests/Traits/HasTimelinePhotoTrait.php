<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use App\Enum\TimelinePhotoGranularity;

trait HasTimelinePhotoTrait
{
	protected ?TimelinePhotoGranularity $photo_timeline = null;

	/**
	 * @return TimelinePhotoGranularity|null
	 */
	public function photo_timeline(): ?TimelinePhotoGranularity
	{
		return $this->photo_timeline;
	}
}
