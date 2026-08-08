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
				'key' => 'managed_cache_enabled',
				'value' => '1',
				'cat' => 'Mod Cache',
				'type_range' => self::BOOL,
				'description' => 'Enable the managed cache for permission-filtered album/photo listings.',
				'details' => 'Independent of "Enable caching of responses given requests" above: this caches individual query results (e.g. sub-album and photo listings) rather than whole HTTP responses, and stays on by default.',
				'is_secret' => false,
				'level' => 0,
			],
			[
				'key' => 'managed_cache_ttl',
				'value' => '3600',
				'cat' => 'Mod Cache',
				'type_range' => self::POSITIVE,
				'description' => 'Number of seconds managed cache entries should be kept.',
				'details' => 'Longer TTL will save more resources but may result in outdated listings until the relevant cache tag is invalidated.',
				'is_secret' => false,
				'level' => 0,
			],
		];
	}
};
