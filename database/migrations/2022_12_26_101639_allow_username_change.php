<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'allow_username_change',
				'value' => '1',
				'cat' => 'config',
				'type_range' => '0|1',
				'confidentiality' => '0',
				'description' => 'Allow users to change their username.',
			],
		];
	}
};
