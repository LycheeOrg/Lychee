<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

/**
 * Feature 075 (FR-075-04): gates whether the v3 album listings expose the
 * two side cover ids behind the album cover. The ids are always computed;
 * this only decides whether they are sent and rendered.
 */
return new class() extends BaseConfigMigration {
	public const CAT = 'Gallery';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'album_hover_side_covers_enabled',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Show two different photos behind the album cover on hover.',
				'details' => 'Applies to album, tag, person and smart album tiles. The side photos follow the album sort order. When disabled, no extra thumbnails are requested.',
				'level' => 0,
				'order' => 16,
				'is_expert' => false,
			],
		];
	}
};
