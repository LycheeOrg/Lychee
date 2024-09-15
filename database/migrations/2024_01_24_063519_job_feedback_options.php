<?php

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const PROCESSING = 'Image Processing';

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
