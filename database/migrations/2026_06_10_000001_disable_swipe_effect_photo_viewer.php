<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'Gallery';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'disable_swipe_effect',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Disable swipe effect on photo viewer',
				'details' => '',
				'level' => 0,
				'order' => 38,
				'is_expert' => false,
			],
		];
	}
};
