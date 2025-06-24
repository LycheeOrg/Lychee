<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CONFIG = 'Mod Welcome';
	public const STRING_REQ = 'string_required';

	public function getConfigs(): array
	{
		DB::table('configs')->where('key', 'landing_background_landscape')->update(['details' => 'This image is also used when sharing the gallery link directly.']);

		return [
			[
				'key' => 'gallery_header_enabled',
				'value' => '0',
				'cat' => self::CONFIG,
				'type_range' => self::BOOL,
				'description' => 'Enable header image in the gallery view',
				'details' => '',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 5,
			],
			[
				'key' => 'gallery_header_logged_in_enabled',
				'value' => '0',
				'cat' => self::CONFIG,
				'type_range' => self::BOOL,
				'description' => 'Enable header image in the gallery view when logged in',
				'details' => '',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 6,
			],
			[
				'key' => 'gallery_header',
				'value' => '',
				'cat' => self::CONFIG,
				'type_range' => self::STRING,
				'description' => 'URL of header image in the gallery view',
				'details' => '',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 7,
			],
			[
				'key' => 'gallery_header_bar_transparent',
				'value' => '1',
				'cat' => self::CONFIG,
				'type_range' => self::BOOL,
				'description' => 'Make the header bar transparent.',
				'details' => 'If enabled, the header bar will be transparent and the header image will be visible behind it.',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 0,
				'order' => 8,
			],
			[
				'key' => 'gallery_header_bar_gradient',
				'value' => '1',
				'cat' => self::CONFIG,
				'type_range' => self::BOOL,
				'description' => 'Add a gradient background to the header bar.',
				'details' => 'If enabled the header bar will have a gradient background aiming to improve the readability of the text, otherwise it will be transparent.',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 0,
				'order' => 8,
			],
		];
	}
};