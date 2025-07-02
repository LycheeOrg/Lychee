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
				'key' => 'force_migration_in_production',
				'value' => '0',
				'cat' => 'Admin',
				'type_range' => self::BOOL,
				'description' => 'Force migration even if app is in production mode',
				'details' => '',
				'is_secret' => false,
				'level' => 0,
				'order' => 4,
				'not_on_docker' => true,
				'is_expert' => true,
			],
		];
	}
};