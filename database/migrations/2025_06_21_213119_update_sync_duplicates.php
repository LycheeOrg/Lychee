<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CONFIG = 'Image Processing';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'skip_duplicates_early',
				'value' => '1',
				'cat' => self::CONFIG,
				'type_range' => self::BOOL,
				'description' => 'Skip duplicate early if found on import',
				'details' => 'Use the photo title to check for duplicate in the target album.',
				'is_secret' => false,
				'level' => 0,
				'order' => 13,
			],
		];
	}
};