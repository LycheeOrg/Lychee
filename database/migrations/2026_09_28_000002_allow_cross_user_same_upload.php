<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'Image Processing';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'skip_duplicates_not_owned',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Skip and reject duplicates if owner does not match.',
				'details' => 'If a user uploads a duplicate photo that they do not own, it will be skipped and rejected. Disabling this will allow uploader to take control of such duplicates.',
				'is_secret' => true,
				'is_expert' => true,
				'level' => 0,
				'order' => 24,
			],
		];
	}
};

