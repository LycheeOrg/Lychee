<?php

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'map_provider',
				'value' => 'Wikimedia',
				'confidentiality' => '0',
				'cat' => 'Mod Map',
				'type_range' => 'Wikimedia|OpenStreetMap.org|OpenStreetMap.de|OpenStreetMap.fr|RRZE',
				'description' => '',
			],
		];
	}
};
