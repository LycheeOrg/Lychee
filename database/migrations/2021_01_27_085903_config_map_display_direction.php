<?php

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'map_display_direction',
				'value' => '1',
				'confidentiality' => '0',
				'cat' => 'Mod Map',
				'type_range' => self::BOOL,
				'description' => '',
			],
		];
	}
};
