<?php

namespace App\Contracts\Http\Requests;

use App\Enum\TimelineAlbumGranularity;

interface HasTimelineAlbum
{
	/**
	 * @return TimelineAlbumGranularity|null
	 */
	public function album_timeline(): ?TimelineAlbumGranularity;
}
