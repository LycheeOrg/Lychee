<?php

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
