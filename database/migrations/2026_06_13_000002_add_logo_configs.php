<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const MOD_WELCOME = 'Mod Welcome';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'site_logo',
				'value' => '',
				'cat' => self::MOD_WELCOME,
				'type_range' => self::STRING,
				'description' => 'URL of logo image in the header bar',
				'details' => 'When set, replaces the website title text in the gallery header bar.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 10,
			],
			[
				'key' => 'landing_logo',
				'value' => '',
				'cat' => self::MOD_WELCOME,
				'type_range' => self::STRING,
				'description' => 'URL of logo image in the landing view',
				'details' => 'When set, replaces the landing title and subtitle text in the landing page intro.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 11,
			],
			[
				'key' => 'landing_header_logo',
				'value' => '',
				'cat' => self::MOD_WELCOME,
				'type_range' => self::STRING,
				'description' => 'URL of logo image in the landing page header',
				'details' => 'When set, replaces the landing title and subtitle text in the top-left corner of the landing page.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 12,
			],
		];
	}
};
