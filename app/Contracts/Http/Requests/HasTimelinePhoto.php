<?php

namespace App\Contracts\Http\Requests;

use App\Enum\TimelinePhotoGranularity;

interface HasTimelinePhoto
{
	/**
	 * @return TimelinePhotoGranularity|null
	 */
	public function photo_timeline(): ?TimelinePhotoGranularity;
}
