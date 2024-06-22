<?php

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'album_subtitle_type',
				'value' => 'oldstyle',
				'confidentiality' => '0',
				'cat' => 'Gallery',
				'type_range' => 'description|takedate|creation|oldstyle',
				'description' => '',
			],
		];
	}
};
