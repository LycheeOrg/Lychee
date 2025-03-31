<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			// [
			// 	'key' => 'disable_small_download',
			// 	'value' => '0',
			// 	'cat' => 'Mod Pro',
			// 	'type_range' => self::BOOL,
			// 	'description' => 'Disable thumbs download.',
			// 	'details' => 'The favourites are persisted in the browser local storage.',
			// 	'is_secret' => false,
			// 	'level' => 1,
			// ],
			// [
			// 	'key' => 'disable_medium_download',
			// 	'value' => '0',
			// 	'cat' => 'Mod Pro',
			// 	'type_range' => self::BOOL,
			// 	'description' => 'Disable thumbs download.',
			// 	'details' => 'The favourites are persisted in the browser local storage.',
			// 	'is_secret' => false,
			// 	'level' => 1,
			// ],
		];
	}
};
