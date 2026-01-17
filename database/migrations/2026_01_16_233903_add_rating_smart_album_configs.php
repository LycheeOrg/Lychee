<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const SMART_ALBUMS = 'Smart Albums';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'smart_albums_unrated_enabled',
				'value' => '1',
				'is_secret' => false,
				'cat' => self::SMART_ALBUMS,
				'type_range' => self::BOOL,
				'description' => 'Enable Unrated smart album.',
			],
			[
				'key' => 'smart_albums_one_star_enabled',
				'value' => '1',
				'is_secret' => false,
				'cat' => self::SMART_ALBUMS,
				'type_range' => self::BOOL,
				'description' => 'Enable 1 Star smart album.',
			],
			[
				'key' => 'smart_albums_two_stars_enabled',
				'value' => '1',
				'is_secret' => false,
				'cat' => self::SMART_ALBUMS,
				'type_range' => self::BOOL,
				'description' => 'Enable 2 Stars smart album.',
			],
			[
				'key' => 'smart_albums_three_stars_enabled',
				'value' => '1',
				'is_secret' => false,
				'cat' => self::SMART_ALBUMS,
				'type_range' => self::BOOL,
				'description' => 'Enable 3 Stars smart album.',
			],
			[
				'key' => 'smart_albums_four_stars_enabled',
				'value' => '1',
				'is_secret' => false,
				'cat' => self::SMART_ALBUMS,
				'type_range' => self::BOOL,
				'description' => 'Enable 4 Stars smart album.',
			],
			[
				'key' => 'smart_albums_five_stars_enabled',
				'value' => '1',
				'is_secret' => false,
				'cat' => self::SMART_ALBUMS,
				'type_range' => self::BOOL,
				'description' => 'Enable 5 Stars smart album.',
			],
			[
				'key' => 'smart_albums_best_pictures_enabled',
				'value' => '1',
				'is_secret' => false,
				'cat' => self::SMART_ALBUMS,
				'type_range' => self::BOOL,
				'description' => 'Enable Best Pictures smart album.',
			],
		];
	}
};
