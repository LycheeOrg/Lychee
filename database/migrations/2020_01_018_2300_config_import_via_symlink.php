<?php

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'import_via_symlink',
				'value' => '0',
				'confidentiality' => '2',
				'cat' => 'Image Processing',
				'type_range' => self::BOOL,
				'description' => '',
			],
		];
	}
};
