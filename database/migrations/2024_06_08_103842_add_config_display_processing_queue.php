<?php

declare(strict_types=1);

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const PROCESSING = 'Image Processing';
	public const BOOL = '0|1';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'job_processing_queue_visible',
				'value' => '1',
				'is_secret' => false,
				'cat' => self::PROCESSING,
				'type_range' => self::BOOL,
				'description' => 'Enable the processing queue in the bottom left corner.',
			],
		];
	}
};
