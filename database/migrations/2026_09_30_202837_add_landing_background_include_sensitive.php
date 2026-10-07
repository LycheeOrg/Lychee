<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'Mod NSFW';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'hide_nsfw_in_landing_page',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Do not show sensitive photos on the Landing Page',
				'details' => 'Pictures placed in sensitive albums will not be shown on the Landing Page.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 32767,
			],
		];
	}
};