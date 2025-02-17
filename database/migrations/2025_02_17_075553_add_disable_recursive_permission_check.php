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
				'key' => 'disable_recursive_permission_check',
				'value' => '1',
				'cat' => 'Admin',
				'type_range' => self::BOOL,
				'description' => 'Disable recursive permission check.',
				'details' => 'Diagnostic page can be slow when there are many albums and photos. This option disables the recursive permission check for speed.',
				'is_secret' => false,
				'level' => 0,
			],
		];
	}
};
