<?php

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const GALLERY = 'Gallery';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'autoplay_enabled',
				'value' => '1',
				'is_secret' => true,
				'cat' => self::GALLERY,
				'type_range' => self::BOOL,
				'description' => 'Set autoplay attribute on videos.',
			],
		];
	}
};
