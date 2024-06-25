<?php

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'use_job_queues',
				'value' => '0',
				'cat' => 'Image Processing',
				'type_range' => self::BOOL,
				'confidentiality' => '0',
				'description' => 'Use job queues instead of directly live connection.',
			],
		];
	}
};
