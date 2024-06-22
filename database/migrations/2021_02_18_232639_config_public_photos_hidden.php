<?php

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'public_photos_hidden',
				'value' => '1',
				'confidentiality' => '2',
				'cat' => 'config',
				'type_range' => self::BOOL,
				'description' => '',
			],
		];
	}
};
