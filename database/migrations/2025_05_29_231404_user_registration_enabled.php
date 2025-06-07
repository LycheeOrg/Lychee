<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'Users Management';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'user_registration_enabled',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Enable user registration.',
				'details' => 'If disabled, new users cannot register themselves.',
				'is_expert' => false,
				'is_secret' => true,
				'level' => 0,
				'order' => 1,
			],
		];
	}
};
