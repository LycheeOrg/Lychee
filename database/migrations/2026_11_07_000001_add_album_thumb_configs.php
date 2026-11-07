<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

/**
 * Feature 110: size and gap of the v8 album tiles
 * (FR-110-01, ADR-110-01; FR-110-16, Q-110-05).
 */
return new class() extends BaseConfigMigration {
	public const CAT = 'Gallery';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'album_thumb_min_width',
				'value' => '175',
				'cat' => self::CAT,
				'type_range' => self::POSITIVE,
				'is_secret' => false,
				'description' => 'Minimum width of album thumbnails (px)',
				'details' => 'Album thumbnails are at least this wide: each row gets as many columns as fit, and the columns share the leftover width. Below 640 px, the number of albums per row on mobile applies.',
				'level' => 0,
				'order' => 26,
				'is_expert' => false,
			],
			[
				'key' => 'album_thumb_gap',
				'value' => '16',
				'cat' => self::CAT,
				'type_range' => self::INT,
				'is_secret' => false,
				'description' => 'Gap between album thumbnails (px)',
				'details' => 'Used from 768 px wide. Between 640 and 767 px the gap is half of it, below 640 px a quarter. 0 lets the thumbnails touch.',
				'level' => 0,
				'order' => 26,
				'is_expert' => false,
			],
		];
	}
};
