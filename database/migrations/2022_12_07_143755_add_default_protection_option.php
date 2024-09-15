<?php

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'default_album_protection',
				'value' => '1',
				'confidentiality' => '0',
				'cat' => 'config',
				'type_range' => '1|2|3',
				'description' => 'Default protection for newly created albums. 1 = private, 2 = public, 3 = inherit from parent',
			],
		];
	}
};
