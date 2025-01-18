<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const SE = 'lychee SE';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'email',
				'value' => '',
				'is_secret' => true,
				'cat' => self::SE,
				'type_range' => self::STRING,
				'description' => 'Email used when requesting the license.',
				'details' => '',
			],
			[
				'key' => 'license_key',
				'value' => '',
				'is_secret' => true,
				'cat' => self::SE,
				'type_range' => self::STRING,
				'description' => 'Lychee License key',
				'details' => 'Get Supporter Edition here: https://lycheeorg.dev/get-supporter-edition',
			],
			[
				'key' => 'disable_se_call_for_actions',
				'value' => '0',
				'is_secret' => false,
				'cat' => self::SE,
				'type_range' => self::BOOL,
				'description' => 'Disable Lychee SE hint',
				'details' => 'Hides Lychee SE call for actions.',
			],
			[
				'key' => 'enable_se_preview',
				'value' => '0',
				'is_secret' => false,
				'cat' => self::SE,
				'type_range' => self::BOOL,
				'description' => 'Enable preview of Lychee SE features',
				'details' => '',
			],
		];
	}
};
