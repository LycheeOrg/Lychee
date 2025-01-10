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
				'key' => 'force_32bit_ids',
				'value' => '0',
				'confidentiality' => '0',
				'cat' => 'config',
				'type_range' => self::BOOL,
				'description' => '',
			],
		];
	}
};
