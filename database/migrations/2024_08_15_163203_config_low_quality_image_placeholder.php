<?php

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'low_quality_image_placeholder',
				'value' => '1',
				'cat' => 'Image Processing',
				'type_range' => self::BOOL,
				'description' => 'Enable low quality image placeholders',
			],
		];
	}
};