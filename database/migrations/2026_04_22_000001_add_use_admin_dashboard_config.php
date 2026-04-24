<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'config';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'use_admin_dashboard',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Use admin dashboard instead of links in Left menu',
				'details' => '',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 0,
			],
		];
	}
};
