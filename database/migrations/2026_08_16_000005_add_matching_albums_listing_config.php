<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'Smart Albums';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'TA_albums_listing_enabled',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Show the list of albums carrying the tag in Tag Albums.',
				'details' => 'When enabled, a Tag Album also lists the real albums tagged with its criteria tag(s), in addition to the matching photos.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 12,
			],
			[
				'key' => 'PA_albums_listing_enabled',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Show the list of albums containing the person in Person Albums.',
				'details' => 'When enabled, a Person Album also lists the real albums containing a matching photo of the person, in addition to the matching photos.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 102,
			],
		];
	}
};
