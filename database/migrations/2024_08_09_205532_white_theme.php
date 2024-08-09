<?php

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CONFIG = 'config';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'dark_mode_enabled',
				'value' => '1',
				'is_secret' => false,
				'cat' => self::CONFIG,
				'type_range' => self::BOOL,
				'description' => 'Use dark mode for Lychee.',
			],
		];
	}
};
