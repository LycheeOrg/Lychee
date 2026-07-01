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
				'key' => 'breadcrumb_enabled',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Enable breadcrumb navigation in the album header',
				'details' => 'Display the album ancestry as breadcrumbs in the header bar instead of the back button and title.',
				'level' => 0,
				'order' => 85,
				'is_expert' => false,
			],
		];
	}
};
