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
				'key' => 'update_check_every_days',
				'value' => '3',
				'confidentiality' => '2',
				'cat' => 'Config',
				'type_range' => self::INT,
				'description' => '',
			],
		];
	}
};
