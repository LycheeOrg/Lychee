<?php

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const PROCESSING = 'Image Processing';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'default_user_quota',
				'value' => '0',
				'cat' => self::PROCESSING,
				'type_range' => self::INT,
				'description' => 'Default space quota for new users.',
				'details' => 'Value in KB, keep at 0 to disable quota.',
				'is_secret' => true,
				'level' => 1,
			],
		];
	}
};
// TOOD: remove legacy setting allow_username_change