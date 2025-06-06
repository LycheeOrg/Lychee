<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'Image Processing';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'enable_colour_extractions',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Extract the 5 most used colours from the image.',
				'details' => '',
				'is_expert' => false,
				'is_secret' => false,
				'level' => 1,
				'order' => 15,
			],
			[
				'key' => 'colour_extraction_driver',
				'value' => 'farzai',
				'cat' => self::CAT,
				'type_range' => 'league|farzai',
				'description' => 'Driver for colour extraction.',
				'details' => 'Slower: league does a full sampling and use ciede2000DeltaE for colour distance calculation.<br>Faster: farzai uses spot sampling and k-mean distance.',
				'is_expert' => true,
				'is_secret' => false,
				'level' => 1,
				'order' => 16,
			],
		];
	}
};