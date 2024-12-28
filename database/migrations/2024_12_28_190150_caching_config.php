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
