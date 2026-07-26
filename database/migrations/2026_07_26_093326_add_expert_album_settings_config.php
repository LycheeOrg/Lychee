<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const BOOL = '0|1';
	public const CAT = 'Gallery';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'expert_album_settings',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Show expert album settings by default',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'details' => 'When enabled, the album settings modal shows advanced fields (header, license, copyright) by default instead of behind an "expert mode" toggle.',
				'order' => 92,
			],
		];
	}
};
