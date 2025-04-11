<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'Mod Pro';

	public function getConfigs(): array
	{
		// landing_background
		return [
			[
				'key' => 'metrics_enabled',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Enable statistics on photos & albums',
				'details' => '',
				'is_expert' => false,
				'is_secret' => true,
				'level' => 1,
				'order' => 2,
			],
		];
	}
};
