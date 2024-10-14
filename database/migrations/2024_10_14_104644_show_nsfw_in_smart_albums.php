<?php

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const OAUTH = 'OAuth & SSO';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'hide_nsfw_photos_in_smart_albums',
				'value' => '1', // safe default
				'cat' => 'Mod NSFW',
				'type_range' => self::BOOL,
				'description' => 'Do not show sensitive photos in Smart Albums.',
				'details' => 'Pictures placed in sensive albums will not be shown in Smart Albums.',
				'is_secret' => false,
				'level' => 0,
			],
		];
	}
};
