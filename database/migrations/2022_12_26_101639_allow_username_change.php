<?php

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'allow_username_change',
				'value' => '1',
				'cat' => 'config',
				'type_range' => '0|1',
				'confidentiality' => '0',
				'description' => 'Allow users to change their username.',
			],
		];
	}
};
