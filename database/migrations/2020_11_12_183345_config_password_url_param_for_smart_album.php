<?php

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'unlock_password_photos_with_url_param',
				'value' => '0',
				'confidentiality' => '2',
				'cat' => 'Smart Albums',
				'type_range' => self::BOOL,
				'description' => '',
			],
		];
	}
};
