<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'low_number_of_shoots_per_day',
				'value' => '10',
				'cat' => 'Gallery',
				'type_range' => self::POSITIVE,
				'description' => 'Number of shoots per day to be considered as low.',
				'details' => 'This is used to determine the color in the punch card statistics.',
				'is_secret' => false,
				'level' => 1,
			],
			[
				'key' => 'medium_number_of_shoots_per_day',
				'value' => '50',
				'cat' => 'Gallery',
				'type_range' => self::POSITIVE,
				'description' => 'Number of shoots per day to be considered as medium.',
				'details' => 'This is used to determine the color in the punch card statistics.',
				'is_secret' => false,
				'level' => 1,
			],
			[
				'key' => 'high_number_of_shoots_per_day',
				'value' => '100',
				'cat' => 'Gallery',
				'type_range' => self::POSITIVE,
				'description' => 'Number of shoots per day to be considered as high.',
				'details' => 'This is used to determine the color in the punch card statistics.',
				'is_secret' => false,
				'level' => 1,
			],
		];
	}
};
