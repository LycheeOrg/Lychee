<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const PROCESSING = 'Image Processing';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'exiftool_path',
				'value' => '',
				'is_secret' => true,
				'cat' => self::PROCESSING,
				'type_range' => 'string',
				'description' => 'Path to the binary of exiftool.',
			],
		];
	}
};
