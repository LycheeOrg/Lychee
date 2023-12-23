<?php

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const MOD_SEARCH = 'Mod Search';
	public const POSITIVE = 'positive';
	public const BOOL = 'bool';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'search_pagination_limit',
				'value' => '1000',
				'confidentiality' => '0',
				'cat' => self::MOD_SEARCH,
				'type_range' => self::POSITIVE,
				'description' => 'Number of results to display per page.',
			],
		];
	}
};
