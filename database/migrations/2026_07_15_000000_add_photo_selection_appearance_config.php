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
				'key' => 'selection_border_enabled',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Show a border around selected photo thumbnails',
				'details' => 'When enabled, selected photo thumbnails are outlined with a border.',
				'level' => 0,
				'order' => 86,
				'is_expert' => true,
			],
			[
				'key' => 'selection_overlay_enabled',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Show a light blue overlay on selected photo thumbnails',
				'details' => 'When enabled, selected photo thumbnails are tinted with a light blue overlay.',
				'level' => 0,
				'order' => 87,
				'is_expert' => true,
			],
		];
	}
};
