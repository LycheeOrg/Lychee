<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const PROCESSING = 'Image Processing';
	public const FRAME_MODE = 'first|middle|custom';
	public const FRAME_SECONDS_RANGE = 'int:0:3600';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'video_thumbnail_frame_mode',
				'value' => 'middle',
				'cat' => self::PROCESSING,
				'type_range' => self::FRAME_MODE,
				'description' => 'Frame to use for the video thumbnail',
				'details' => 'First: always the very first frame (0s), which can be black on some videos. Middle: the frame halfway through the video, falling back to 0s if the duration is unknown. Custom: the fixed number of seconds set below.',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 0,
				'order' => 93,
			],
			[
				'key' => 'video_thumbnail_frame_seconds',
				'value' => '1',
				'cat' => self::PROCESSING,
				'type_range' => self::FRAME_SECONDS_RANGE,
				'description' => 'Timing frame (in seconds) used for video thumbnails',
				'details' => 'Only used when "Frame to use for the video thumbnail" is set to custom.',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 0,
				'order' => 94,
			],
		];
	}
};
