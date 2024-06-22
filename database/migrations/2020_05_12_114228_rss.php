<?php

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'rss_enable',
				'value' => '0',
				'confidentiality' => '0',
				'cat' => 'Mod RSS',
				'type_range' => self::BOOL,
				'description' => '',
			],
			[
				'key' => 'rss_recent_days',
				'value' => '7',
				'confidentiality' => '0',
				'cat' => 'Mod RSS',
				'type_range' => self::INT,
				'description' => '',
			],
			[
				'key' => 'rss_max_items',
				'value' => '100',
				'confidentiality' => '0',
				'cat' => 'Mod RSS',
				'type_range' => self::INT,
				'description' => '',
			],
		];
	}
};
