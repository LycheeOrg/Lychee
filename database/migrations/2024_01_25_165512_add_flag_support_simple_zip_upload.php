<?php

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CONFIG = 'Image Processing';
	public const BOOL = '0|1';
	public const STRING = 'string';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'extract_zip_files',
				'value' => '0',
				'confidentiality' => '2',
				'cat' => self::CONFIG,
				'type_range' => self::BOOL,
				'description' => 'Uploaded Zip files will be extracted and contained pictures will be imported (0 | 1).',
			],
		];
	}
};
