<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CONFIG = 'config';
	public const BACK_BUTTON = 'Mod Back Button';
	public const ENUM = 'left|right';
	public const STRING = 'string';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'login_button_position',
				'value' => 'left',
				'confidentiality' => '0',
				'cat' => self::CONFIG,
				'type_range' => self::ENUM,
				'description' => 'Position of the login button (left | right)',
			],
			[
				'key' => 'back_button_enabled',
				'value' => '0',
				'confidentiality' => '0',
				'cat' => self::BACK_BUTTON,
				'type_range' => self::BOOL,
				'description' => 'Enable/disable back button on gallery (0 | 1)',
			],
			[
				'key' => 'back_button_text',
				'value' => 'Return to Home',
				'confidentiality' => '0',
				'cat' => self::BACK_BUTTON,
				'type_range' => self::STRING,
				'description' => 'Text of the back button (will be positioned opposite to Login)',
			],
			[
				'key' => 'back_button_url',
				'value' => '/',
				'confidentiality' => '0',
				'cat' => self::BACK_BUTTON,
				'type_range' => self::STRING,
				'description' => 'Link of the back button',
			],
		];
	}
};
