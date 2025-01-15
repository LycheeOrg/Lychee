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
				'key' => 'zip_deflate_level',
				'value' => '6',
				'confidentiality' => '0',
				'cat' => 'config',
				'type_range' => '-1|0|1|2|3|4|5|6|7|8|9',
				'description' => 'DEFLATE compression level: -1 = disable compression (use STORE method), 0 = no compression (use DEFLATE method), 1 = minimal compression (fast), ... 9 = maximum compression (slow)',
			],
		];
	}
};
