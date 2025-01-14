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
				'key' => 'use_job_queues',
				'value' => '0',
				'cat' => 'Image Processing',
				'type_range' => self::BOOL,
				'confidentiality' => '0',
				'description' => 'Use job queues instead of directly live connection.',
			],
		];
	}
};
