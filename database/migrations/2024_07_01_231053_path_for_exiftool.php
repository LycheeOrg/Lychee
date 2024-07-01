<?php

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const PROCESSING = 'Image Processing';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'exiftool_path',
				'value' => '',
				'is_secret' => true,
				'cat' => self::PROCESSING,
				'type_range' => 'string',
				'description' => 'Path to the binary of exiftool.',
			],
		];
	}
};
