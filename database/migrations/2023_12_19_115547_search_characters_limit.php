<?php

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const MOD_SEARCH = 'Mod Search';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'search_minimum_length_required',
				'value' => '4',
				'confidentiality' => '0',
				'cat' => self::MOD_SEARCH,
				'type_range' => self::POSITIVE,
				'description' => 'Number of characters required to trigger search (default: 4).',
			],
		];
	}
};
