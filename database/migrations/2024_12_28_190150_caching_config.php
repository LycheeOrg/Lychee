<?php

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'cache_enabled',
				'value' => '1',
				'cat' => 'Mod Cache',
				'type_range' => self::BOOL,
				'description' => 'Enable caching of responses given requests.',
				'details' => 'This will significantly speed up the response time of Lychee.',
				'is_secret' => false,
				'level' => 0,
			],
			[
				'key' => 'cache_event_logging',
				'value' => '1', // TODO: flip to false
				'cat' => 'Mod Cache',
				'type_range' => self::BOOL,
				'description' => 'Add log lines for events related to caching.',
				'details' => 'This may result in large amount of logs',
				'is_secret' => true,
				'level' => 0,
			],
			[
				'key' => 'cache_ttl',
				'value' => '60',
				'cat' => 'Mod Cache',
				'type_range' => self::POSITIVE,
				'description' => 'Number of seconds responses should be cached.',
				'details' => '',
				'is_secret' => false,
				'level' => 1,
			],
		];
	}
};
