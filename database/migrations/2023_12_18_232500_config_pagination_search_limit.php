<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const MOD_SEARCH = 'Mod Search';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'search_pagination_limit',
				'value' => '1000',
				'confidentiality' => '0',
				'cat' => self::MOD_SEARCH,
				'type_range' => self::POSITIVE,
				'description' => 'Number of results to display per page.',
			],
		];
	}
};
