<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const GALLERY = 'Gallery';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'thumb_min_max_order',
				'value' => 'younger_older',
				'is_secret' => true,
				'cat' => self::GALLERY,
				'type_range' => 'older_younger|younger_older',
				'description' => 'Set which date to display first in thumb. Allowed values: older_younger, younger_older',
			],
			[
				'key' => 'header_min_max_order',
				'value' => 'older_younger',
				'is_secret' => true,
				'cat' => self::GALLERY,
				'type_range' => 'older_younger|younger_older',
				'description' => 'Set which date to display first in header. Allowed values: older_younger, younger_older',
			],
		];
	}
};
