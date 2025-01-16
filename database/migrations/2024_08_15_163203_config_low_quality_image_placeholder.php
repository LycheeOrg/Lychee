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
				'key' => 'low_quality_image_placeholder',
				'value' => '1',
				'cat' => self::PROCESSING,
				'type_range' => self::BOOL,
				'description' => 'Enable low quality image placeholders',
				'is_secret' => false,
			],
		];
	}
};