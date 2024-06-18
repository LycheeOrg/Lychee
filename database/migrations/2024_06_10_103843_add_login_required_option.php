<?php

declare(strict_types=1);

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const GALLERY = 'Gallery';
	public const BOOL = '0|1';

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
