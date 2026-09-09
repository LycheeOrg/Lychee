<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

/**
 * Instance-wide-only configuration governing how a `TITLE`-sorted album's
 * direct **photos** compute their `bucket_id`
 * ({@see \App\Services\PhotoBucketComputer}).
 *
 * Deliberately a fully independent pair from the album-only
 * `title_bucket_mode`/`title_bucket_prefix_length` (`2026_09_05_120002_add_title_bucket_configs.php`),
 * per explicit user direction: changing one must never affect the other.
 */
return new class() extends BaseConfigMigration {
	public const CAT = 'Gallery';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'photo_title_bucket_mode',
				'value' => 'date_prefix',
				'cat' => self::CAT,
				'type_range' => 'date_prefix|alphabetical',
				'is_secret' => false,
				'description' => 'How TITLE-sorted albums bucket their direct photos for the virtual-scroll timeline',
				'details' => '"date_prefix" parses a leading date out of the photo title (e.g. "2020-03 Vacation"); "alphabetical" buckets by the first N characters of the title instead. Independent from the album-only title_bucket_mode.',
				'level' => 0,
				'order' => 12,
				'is_expert' => true,
			],
			[
				'key' => 'photo_title_bucket_prefix_length',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::POSITIVE,
				'is_secret' => false,
				'description' => 'Number of leading title characters used as the bucket key when photo_title_bucket_mode is alphabetical',
				'details' => '',
				'level' => 0,
				'order' => 13,
				'is_expert' => true,
			],
		];
	}
};
