<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'config';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'is_embed_enabled',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Enable the embed API endpoints and UI features for external website integration.',
				'details' => 'When disabled, all embed API endpoints return 404 and embed-related UI features are hidden.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 100,
			],
		];
	}
};
