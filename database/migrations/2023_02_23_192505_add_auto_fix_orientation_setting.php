<?php

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'auto_fix_orientation',
				'value' => '1',
				'cat' => 'Image Processing',
				'type_range' => self::BOOL,
				'confidentiality' => '0',
				'description' => 'Automatically rotate imported images',
			],
		];
	}
};
