<?php

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'local_takestamp_video_formats',
				'value' => '.avi|.mov',
				'confidentiality' => '2',
				'cat' => 'Image Processing',
				'type_range' => '',
				'description' => '',
			],
		];
	}
};
