<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'maintenance_processing_limit',
				'value' => '5000',
				'cat' => 'Admin',
				'type_range' => self::POSITIVE,
				'description' => 'Number of maintenance operations to execute.',
				'details' => 'Larger number will process more items in one go, but may cause timeouts.',
				'is_secret' => false,
				'level' => 0,
			],
		];
	}
};
