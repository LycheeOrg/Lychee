<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'Gallery';

	public function getConfigs(): array
	{
		// landing_background
		return [
			[
				'key' => 'photo_thumb_info',
				'value' => 'title',
				'cat' => self::CAT,
				'type_range' => 'title|description',
				'description' => 'Select the info shown in photo thumbnail',
				'details' => 'If description is selected, the date will not be shown either.',
				'is_expert' => true,
				'is_secret' => true,
				'level' => 0,
				'order' => 35,
			],
		];
	}
};