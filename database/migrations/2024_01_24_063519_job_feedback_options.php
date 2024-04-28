<?php

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const PROCESSING = 'Image Processing';
	public const BOOL = '0|1';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'current_job_processing_visible',
				'value' => '1',
				'confidentiality' => '0',
				'cat' => self::PROCESSING,
				'type_range' => self::BOOL,
				'description' => 'Make the processing job queue visible by default (0|1).',
			],
		];
	}
};
