<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const SE = 'lychee SE';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'white_label_enabled',
				'value' => '0',
				'is_secret' => true,
				'cat' => self::SE,
				'type_range' => self::BOOL,
				'description' => 'Hide Lychee branding (white label mode)',
				'details' => 'When enabled, hides the Lychee name, links, and generator metadata from the UI.',
			],
		];
	}
};
