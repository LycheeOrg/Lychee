<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'photo_thumb_tags_enabled',
				'value' => '0',
				'cat' => 'Gallery',
				'type_range' => self::BOOL,
				'description' => 'Display the tags on the photo thumbnail in the album view',
				'details' => 'If description is enabled on the photo thumbnail, the tags will not be displayed.',
				'is_secret' => false,
				'level' => 1,
				'order' => 35, // We put it at the same order as photo_thumb_info
				'not_on_docker' => false,
				'is_expert' => true,
			],
		];
	}
};