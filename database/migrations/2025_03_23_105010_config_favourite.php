<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'client_side_favourite_enabled',
				'value' => '0',
				'cat' => 'Mod Pro',
				'type_range' => self::BOOL,
				'description' => 'Allow visitors to mark pictures as their favourite.',
				'details' => 'The favourites are persisted in the browser local storage.',
				'is_secret' => false,
				'level' => 1,
			],
		];
	}
};
