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
				'key' => 'autoplay_enabled',
				'value' => '1',
				'is_secret' => true,
				'cat' => self::GALLERY,
				'type_range' => self::BOOL,
				'description' => 'Set autoplay attribute on videos.',
			],
		];
	}
};
