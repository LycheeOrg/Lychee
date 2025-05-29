<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'Gallery';

	public function getConfigs(): array
	{
		// landing_background
		return [
			[
				'key' => 'slideshow_enabled',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Enable the slideshow functionality.',
				'details' => '',
				'is_expert' => true,
				'is_secret' => true,
				'level' => 0,
				'order' => 37,
			],
		];
	}
};