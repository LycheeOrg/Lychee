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
				'key' => 'managed_cache_albums_enabled',
				'value' => '1',
				'cat' => 'Mod Cache',
				'type_range' => self::BOOL,
				'description' => 'Enable the managed cache specifically for album-listing queries.',
				'details' => 'Subordinate to "Enable managed cache" above: both must be enabled for album-listing caching (sub-albums, root gallery, tag detail page) to take effect. Lets album-listing caching be disabled independently of any other future managed-cache consumer.',
				'is_secret' => false,
				'level' => 0,
			],
		];
	}
};
