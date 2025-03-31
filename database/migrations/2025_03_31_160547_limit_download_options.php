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
				'key' => 'disable_thumb_download',
				'value' => '1',
				'cat' => 'access_rights',
				'type_range' => self::BOOL,
				'description' => 'Disable squared thumbs download.',
				'details' => '',
				'is_expert' => true,
				'is_secret' => false,
				'level' => 0,
				'order' => 7,
			],
			[
				'key' => 'disable_thumb2x_download',
				'value' => '1',
				'cat' => 'access_rights',
				'type_range' => self::BOOL,
				'description' => 'Disable HiDPI squared thumbs download.',
				'details' => '',
				'is_expert' => true,
				'is_secret' => false,
				'level' => 0,
				'order' => 8,
			],
			[
				'key' => 'disable_small_download',
				'value' => '0',
				'cat' => 'access_rights',
				'type_range' => self::BOOL,
				'description' => 'Disable thumbs download.',
				'details' => '',
				'is_expert' => true,
				'is_secret' => false,
				'level' => 1,
				'order' => 9,
			],
			[
				'key' => 'disable_small2x_download',
				'value' => '0',
				'cat' => 'access_rights',
				'type_range' => self::BOOL,
				'description' => 'Disable HiDPI thumbs download.',
				'details' => '',
				'is_expert' => true,
				'is_secret' => false,
				'level' => 1,
				'order' => 10,
			],
			[
				'key' => 'disable_medium_download',
				'value' => '0',
				'cat' => 'access_rights',
				'type_range' => self::BOOL,
				'description' => 'Disable Medium download.',
				'details' => '',
				'is_expert' => true,
				'is_secret' => false,
				'level' => 1,
				'order' => 11,
			],
			[
				'key' => 'disable_medium2x_download',
				'value' => '0',
				'cat' => 'access_rights',
				'type_range' => self::BOOL,
				'description' => 'Disable HiDPI Medium download.',
				'details' => '',
				'is_expert' => true,
				'is_secret' => false,
				'level' => 1,
				'order' => 12,
			],
		];
	}
};
