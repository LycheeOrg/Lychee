<?php

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'prefer_available_xmp_metadata',
				'value' => '0',
				'confidentiality' => '2',
				'cat' => 'Image Processing',
				'type_range' => self::BOOL,
				'description' => '',
			],
		];
	}
};
