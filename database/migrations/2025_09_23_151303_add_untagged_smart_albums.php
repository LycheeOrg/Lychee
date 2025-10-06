<?php

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const SMART_ALBUMS = 'Smart Albums';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'enable_untagged',
				'value' => '1',
				'is_secret' => false,
				'cat' => self::SMART_ALBUMS,
				'type_range' => self::BOOL,
				'description' => 'Enable Untagged smart album.',
			],
			[
				'key' => 'untagged_photos_pagination_limit',
				'value' => '200',
				'is_secret' => false,
				'cat' => self::SMART_ALBUMS,
				'type_range' => self::POSITIVE,
				'description' => 'Number of photos to display per page in untagged album.',
				'details' => '',
				'level' => 0,
			],
		];
	}
};
