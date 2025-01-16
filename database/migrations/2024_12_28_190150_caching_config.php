<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'cache_enabled',
				'value' => '0',
				'cat' => 'Mod Cache',
				'type_range' => self::BOOL,
				'description' => 'Enable caching of responses given requests.',
				'details' => 'This will significantly speed up the response time of Lychee. <span class="pi pi-exclamation-triangle text-orange-500"></span> If you are using password protected albums, you should not enable this.',
				'is_secret' => false,
				'level' => 0,
			],
			[
				'key' => 'cache_event_logging',
				'value' => '0',
				'cat' => 'Mod Cache',
				'type_range' => self::BOOL,
				'description' => 'Add log lines for events related to caching.',
				'details' => 'This may result in large amount of logs',
				'is_secret' => true,
				'level' => 0,
			],
			[
				'key' => 'cache_ttl',
				'value' => '300',
				'cat' => 'Mod Cache',
				'type_range' => self::POSITIVE,
				'description' => 'Number of seconds responses should be cached.',
				'details' => 'Longer TTL will save more resources but may result in outdated responses.',
				'is_secret' => false,
				'level' => 1,
			],
		];
	}
};
