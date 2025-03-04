<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const TIMELINE = 'Mod Timeline';
	public const CONFIG = 'config';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'timeline_page_enabled',
				'value' => '1',
				'cat' => self::TIMELINE,
				'type_range' => self::BOOL,
				'description' => 'Enable timeline page',
				'details' => '',
				'is_secret' => false,
				'level' => 0,
			],
			[
				'key' => 'home_page_default',
				'value' => 'gallery',
				'cat' => self::CONFIG,
				'type_range' => 'timeline|gallery',
				'description' => 'Default home page after landing',
				'details' => '',
				'is_secret' => false,
				'level' => 0,
			],
		];
	}
};