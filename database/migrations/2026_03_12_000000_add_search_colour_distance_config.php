<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'search_colour_distance',
				'value' => '30',
				'cat' => 'Mod Search',
				'type_range' => self::INT,
				'description' => 'Maximum Manhattan RGB distance for palette colour matching.',
				'details' => 'ABS(c.R-R0)+ABS(c.G-G0)+ABS(c.B-B0) <= this value.',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 0,
			],
		];
	}
};
