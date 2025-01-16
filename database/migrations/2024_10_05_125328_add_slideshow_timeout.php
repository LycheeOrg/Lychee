<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const GALLERY = 'Gallery';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'slideshow_timeout',
				'value' => '5',
				'is_secret' => false,
				'cat' => self::GALLERY,
				'type_range' => self::POSITIVE,
				'description' => 'Refresh rate of the slideshow in seconds.',
				'details' => 'Show next picture after x seconds.',
			],
		];
	}
};
