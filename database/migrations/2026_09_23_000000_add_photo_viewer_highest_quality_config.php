<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'Gallery';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'photo_viewer_highest_quality_enabled',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Always show the highest-quality image in the full-screen photo viewer',
				'details' => 'When enabled, the full-screen viewer renders the best size variant that exists for each photo (typically Original) instead of Medium. Increases bandwidth usage per photo view.',
				'level' => 0,
				'order' => 106,
				'is_expert' => false,
			],
		];
	}
};
