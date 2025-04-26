<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'access_permissions';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'disable_thumb_download',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Disable the download of squared thumbs',
				'details' => '',
				'is_expert' => true,
				'is_secret' => false,
				'level' => 0,
				'order' => 7,
			],
			[
				'key' => 'disable_thumb2x_download',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Disable the download of HiDPI squared thumbs',
				'details' => '',
				'is_expert' => true,
				'is_secret' => false,
				'level' => 0,
				'order' => 8,
			],
			[
				'key' => 'disable_small_download',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Disable the download of thumbs',
				'details' => '',
				'is_expert' => true,
				'is_secret' => false,
				'level' => 1,
				'order' => 9,
			],
			[
				'key' => 'disable_small2x_download',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Disable the download of HiDPI thumbs',
				'details' => '',
				'is_expert' => true,
				'is_secret' => false,
				'level' => 1,
				'order' => 10,
			],
			[
				'key' => 'disable_medium_download',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Disable the download of Medium',
				'details' => '',
				'is_expert' => true,
				'is_secret' => false,
				'level' => 1,
				'order' => 11,
			],
			[
				'key' => 'disable_medium2x_download',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Disable the download of HiDPI Medium',
				'details' => '',
				'is_expert' => true,
				'is_secret' => false,
				'level' => 1,
				'order' => 12,
			],
		];
	}
};
