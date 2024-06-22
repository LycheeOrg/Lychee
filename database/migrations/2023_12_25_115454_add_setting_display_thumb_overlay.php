<?php

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const GALLERY = 'Gallery';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'display_thumb_album_overlay',
				'value' => 'always',
				'confidentiality' => '0',
				'cat' => self::GALLERY,
				'type_range' => 'always|hover|never',
				'description' => 'Display the title and metadata on album thumbs (always|hover|never)',
			],
			[
				'key' => 'display_thumb_photo_overlay',
				'value' => 'hover',
				'confidentiality' => '0',
				'cat' => self::GALLERY,
				'type_range' => 'always|hover|never',
				'description' => 'Display the title and metadata on album thumbs (always|hover|never)',
			],
		];
	}
};
