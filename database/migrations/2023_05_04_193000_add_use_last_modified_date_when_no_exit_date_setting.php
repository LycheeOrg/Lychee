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
				'key' => 'use_last_modified_date_when_no_exif_date',
				'value' => '0',
				'cat' => 'Image Processing',
				'type_range' => '0|1',
				'confidentiality' => '0',
				'description' => 'Use the file\'s last modified time when Exif data has no creation date',
			],
		];
	}
};
