<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'Smart Albums';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'TA_separate_category',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Show Tag Albums in their own category.',
				'details' => 'When enabled, the gallery lists Tag Albums in a separate category instead of among the Smart Albums.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 13,
			],
			[
				'key' => 'PA_separate_category',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Show Person Albums in their own category.',
				'details' => 'When enabled, the gallery lists Person Albums in a separate category instead of among the Smart Albums.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 103,
			],
		];
	}
};
