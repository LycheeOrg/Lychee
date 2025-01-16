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
				'key' => 'show_keybinding_help_popup',
				'value' => '1',
				'is_secret' => false,
				'cat' => 'config',
				'type_range' => self::BOOL,
				'description' => 'Display keybinding help pop-up on login.',
			],
			[
				'key' => 'show_keybinding_help_button',
				'value' => '1',
				'is_secret' => false,
				'cat' => 'config',
				'type_range' => self::BOOL,
				'description' => 'Show keybinding help button in header.',
			],
		];
	}
};

