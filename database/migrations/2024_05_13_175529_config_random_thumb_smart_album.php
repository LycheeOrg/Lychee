<?php

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const PROCESSING = 'Smart Albums';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'SA_random_thumbs',
				'value' => '0',
				'is_secret' => false,
				'cat' => self::PROCESSING,
				'type_range' => self::BOOL,
				'description' => 'Use random thumbs instead of stared/sorting order.',
			],
		];
	}
};
