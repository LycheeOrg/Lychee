<?php

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'new_photos_notification',
				'value' => '0',
				'confidentiality' => '0',
				'cat' => 'config',
				'type_range' => self::BOOL,
				'description' => '',
			],
		];
	}
};
