<?php

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
