<?php

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'swipe_tolerance_x',
				'value' => '150',
				'confidentiality' => '0',
				'cat' => 'Gallery',
				'type_range' => self::INT,
				'description' => '',
			],
			[
				'key' => 'swipe_tolerance_y',
				'value' => '250',
				'confidentiality' => '0',
				'cat' => 'Gallery',
				'type_range' => self::INT,
				'description' => '',
			],
		];
	}
};
