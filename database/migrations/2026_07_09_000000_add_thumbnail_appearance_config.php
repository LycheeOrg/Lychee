<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'Gallery';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'rounded_corners_enabled',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Round the corners of photo and album thumbnails',
				'details' => 'When disabled, photo and album thumbnails are displayed with square corners instead.',
				'level' => 0,
				'order' => 36,
				'is_expert' => false,
			],
			[
				'key' => 'album_border_enabled',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Show a border around photo and album thumbnails',
				'details' => 'Restores the border thumbnails used to have around them.',
				'level' => 0,
				'order' => 37,
				'is_expert' => false,
			],
		];
	}
};
