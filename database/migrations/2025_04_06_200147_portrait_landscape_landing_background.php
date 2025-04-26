<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'Mod Welcome';

	public function getConfigs(): array
	{
		// landing_background
		return [
			[
				'key' => 'landing_background_landscape',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::STRING,
				'description' => 'URL of background image for landscape orientation',
				'details' => '',
				'is_expert' => false,
				'is_secret' => true,
				'level' => 0,
				'order' => 3,
			],
			[
				'key' => 'landing_background_portrait',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::STRING,
				'description' => 'URL of background image for portrait orientation',
				'details' => '',
				'is_expert' => false,
				'is_secret' => true,
				'level' => 0,
				'order' => 4,
			],
		];
	}
};
