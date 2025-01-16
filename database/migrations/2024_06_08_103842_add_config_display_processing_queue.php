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
				'key' => 'job_processing_queue_visible',
				'value' => '1',
				'is_secret' => false,
				'cat' => self::PROCESSING,
				'type_range' => self::BOOL,
				'description' => 'Enable the processing queue in the bottom left corner.',
			],
		];
	}
};
