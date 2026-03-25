<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'webhook';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'webhook_timeout_seconds',
				'value' => '10',
				'cat' => self::CAT,
				'type_range' => self::INT,
				'description' => 'Timeout in seconds for outgoing webhook HTTP requests',
				'details' => 'Controls how long Lychee waits for a webhook endpoint to respond before treating the request as failed. Default is 10 seconds.',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 1,
				'order' => 1,
			],
		];
	}
};
