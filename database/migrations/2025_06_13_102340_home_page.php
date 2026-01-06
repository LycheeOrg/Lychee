<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const TIMELINE = 'Mod Timeline';
	public const CONFIG = 'config';
	public const STRING_REQ = 'string_required';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'home_page_default',
				'value' => 'gallery',
				'cat' => self::CONFIG,
				'type_range' => 'gallery', // We will change the type_range later when adding for functionalities.
				'description' => 'Default home page after landing',
				'details' => '',
				'is_secret' => false,
				'level' => 0,
				'order' => 4,
			],
		];
	}
};