<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Traits;

use App\Enum\TimelineAlbumGranularity;
use App\Enum\TimelinePhotoGranularity;
use App\Models\Configs;

trait HasTimelineData
{
	private function getAlbumTimeline(?TimelineAlbumGranularity $candidate): TimelineAlbumGranularity
	{
		$default_timeline_album_granularity = Configs::getValueAsEnum('timeline_albums_granularity', TimelineAlbumGranularity::class);

		if ($candidate === TimelineAlbumGranularity::DEFAULT || $candidate === TimelineAlbumGranularity::DISABLED) {
			return $default_timeline_album_granularity;
		}

		return $candidate ?? $default_timeline_album_granularity;
	}

	private function getPhotoTimeline(?TimelinePhotoGranularity $candidate): TimelinePhotoGranularity
	{
		$default_timeline_photos_granularity = Configs::getValueAsEnum('timeline_photos_granularity', TimelinePhotoGranularity::class);

		if ($candidate === TimelinePhotoGranularity::DEFAULT || $candidate === TimelinePhotoGranularity::DISABLED) {
			return $default_timeline_photos_granularity;
		}

		return $candidate ?? $default_timeline_photos_granularity;
	}
}