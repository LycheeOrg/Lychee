<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const SQUARE = 'square';
	public const JUSTIFIED = 'justified';
	public const MASONRY = 'masonry';
	public const GRID = 'grid';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'hide_nsfw_in_smart_albums',
				'value' => '1', // safe default
				'cat' => 'Smart Albums',
				'type_range' => self::BOOL,
				'description' => 'Do not show sensitive photos in Smart Albums',
				'details' => 'Pictures placed in sensive albums will not be shown in Smart Albums.',
				'is_secret' => false,
				'level' => 0,
			],
			[
				'key' => 'hide_nsfw_in_frame',
				'value' => '1', // safe default
				'cat' => 'Mod Frame',
				'type_range' => self::BOOL,
				'description' => 'Do not show sensitive photos in Frame',
				'details' => 'Pictures placed in sensive albums will not be shown on the Frame.',
				'is_secret' => false,
				'level' => 0,
			],
			[
				'key' => 'hide_nsfw_in_map',
				'value' => '1', // safe default
				'cat' => 'Mod Map',
				'type_range' => self::BOOL,
				'description' => 'Do not show sensitive photos in Map',
				'details' => 'Pictures placed in sensive albums will not be shown on the Map.',
				'is_secret' => false,
				'level' => 0,
			],
			[
				'key' => 'hide_nsfw_in_search',
				'value' => '1', // safe default
				'cat' => 'Mod Search',
				'type_range' => self::BOOL,
				'description' => 'Do not show sensitive photos in Search',
				'details' => 'Pictures placed in sensive albums will not be shown in Search.',
				'is_secret' => false,
				'level' => 0,
			],
			[
				'key' => 'search_photos_layout',
				'value' => self::SQUARE,
				'cat' => 'Mod Search',
				'type_range' => self::SQUARE . '|' . self::JUSTIFIED . '|' . self::MASONRY . '|' . self::GRID,
				'description' => 'Photo layout for search page',
				'details' => '',
				'is_secret' => false,
				'level' => 0,
			],
			[
				'key' => 'hide_nsfw_in_rss',
				'value' => '1', // safe default
				'cat' => 'Mod RSS',
				'type_range' => self::BOOL,
				'description' => 'Do not show sensitive photos in RSS',
				'details' => 'Pictures placed in sensive albums will not be shown in the RSS feed.',
				'is_secret' => false,
				'level' => 0,
			],
			[
				'key' => 'hide_nsfw_in_timeline',
				'value' => '1', // safe default
				'cat' => 'Mod Timeline',
				'type_range' => self::BOOL,
				'description' => 'Do not show sensitive photos in Timeline',
				'details' => 'Pictures placed in sensive albums will not be shown in the timeline page.',
				'is_secret' => false,
				'level' => 0,
			],
		];
	}
};

