<?php

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const PROCESSING = 'Image Processing';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'extract_zip_on_upload',
				'value' => '0',
				'cat' => self::PROCESSING,
				'type_range' => self::BOOL,
				'description' => 'Extract uploaded zip file and import content.',
				'details' => 'Zip file will stay on your server unless it is properly extracted without faults (after which it is removed).',
				'is_secret' => false,
				'level' => 1, // Only for SE.
			],
		];
	}
};
