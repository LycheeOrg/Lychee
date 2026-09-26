<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

/**
 * Feature 071 (FR-071-01): global default for the album date scrubber rail.
 * A per-album override lives on `base_albums.is_date_scrubber_enabled`.
 */
return new class() extends BaseConfigMigration {
	public const GALLERY = 'Gallery';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'album_date_scrubber_enabled',
				'value' => '1',
				'cat' => self::GALLERY,
				'type_range' => self::BOOL,
				'description' => 'Show the date scrubber on album views',
				'details' => 'Shows the timeline date rail on the right of albums that contain only photos or only sub-albums, ordered by date. Can be overridden per album.',
				'is_secret' => false,
				'level' => 0,
			],
		];
	}
};
