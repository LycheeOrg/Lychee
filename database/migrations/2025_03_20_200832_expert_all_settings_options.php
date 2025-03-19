<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'default_old_settings',
				'value' => '0',
				'cat' => 'Admin',
				'type_range' => self::BOOL,
				'description' => 'Settings view as text input by default.',
				'details' => '',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
			],
			[
				'key' => 'default_expert_settings',
				'value' => '0',
				'cat' => 'Admin',
				'type_range' => self::BOOL,
				'description' => 'Expert settings view enabled by default.',
				'details' => '',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
			],
			[
				'key' => 'default_all_settings',
				'value' => '0',
				'cat' => 'Admin',
				'type_range' => self::BOOL,
				'description' => 'Show all settings in one page.',
				'details' => '',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
			],
		];
	}
};
