<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const OAUTH = 'OAuth & SSO';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'default_user_quota',
				'value' => '0',
				'cat' => self::OAUTH,
				'type_range' => self::INT,
				'description' => 'Default space quota for new users.',
				'details' => 'Value in KB, keep at 0 to disable quota.',
				'is_secret' => true,
				'level' => 1,
			],
		];
	}
};
