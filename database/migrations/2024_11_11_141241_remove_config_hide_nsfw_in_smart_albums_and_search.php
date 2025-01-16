<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigrationReversed;

return new class() extends BaseConfigMigrationReversed {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'hide_nsfw_in_smart_albums_and_search',
				'value' => '1', // safe default
				'cat' => 'Mod NSFW',
				'type_range' => self::BOOL,
				'description' => 'Do not show sensitive photos in Smart Albums and Search.',
				'details' => 'Pictures placed in sensive albums will not be shown in Smart Albums and Search.',
				'is_secret' => false,
				'level' => 0,
			],
		];
	}
};
