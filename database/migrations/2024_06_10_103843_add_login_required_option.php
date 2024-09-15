<?php

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const GALLERY = 'Gallery';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'login_required',
				'value' => '0',
				'is_secret' => false,
				'cat' => self::GALLERY,
				'type_range' => self::BOOL,
				'description' => 'Require user to login to access gallery.',
			],
		];
	}
};
