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
				'key' => 'number_albums_per_row_mobile',
				'value' => '3', // safe default
				'cat' => 'Gallery',
				'type_range' => '1|2|3',
				'description' => 'Number of albums per row on mobile view',
				'details' => '',
				'is_secret' => false,
				'level' => 1,
			],
		];
	}
};
