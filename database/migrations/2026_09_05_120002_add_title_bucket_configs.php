<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

/**
 * Instance-wide-only configuration governing how  a `TITLE`-sorted parent's
 * direct children compute their `bucket_id` (`RecomputeAlbumStatsJob::computeBucket()`).
 * Unlike `album_sorting_col`/`album_timeline`, these two keys have no per-album override.
 */
return new class() extends BaseConfigMigration {
	public const CAT = 'Gallery';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'title_bucket_mode',
				'value' => 'date_prefix',
				'cat' => self::CAT,
				'type_range' => 'date_prefix|alphabetical',
				'is_secret' => false,
				'description' => 'How TITLE-sorted albums bucket their direct children for the virtual-scroll timeline',
				'details' => '"date_prefix" parses a leading date out of the child title (e.g. "2020-03 Vacation"); "alphabetical" buckets by the first N characters of the title instead.',
				'level' => 0,
				'order' => 9,
				'is_expert' => true,
			],
			[
				'key' => 'title_bucket_prefix_length',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::POSITIVE,
				'is_secret' => false,
				'description' => 'Number of leading title characters used as the bucket key when title_bucket_mode is alphabetical',
				'details' => '',
				'level' => 0,
				'order' => 10,
				'is_expert' => true,
			],
		];
	}
};
