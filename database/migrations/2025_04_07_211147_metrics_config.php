<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'Mod Pro';

	public function getConfigs(): array
	{
		// landing_background
		return [
			[
				'key' => 'metrics_enabled',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Enable statistics on photos & albums',
				'details' => 'If enabled, anonymours users will be measured.',
				'is_expert' => false,
				'is_secret' => true,
				'level' => 1,
				'order' => 2,
			],
			[
				'key' => 'metrics_logged_in_users_enabed',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Enable statistics for logged-in users',
				'details' => 'If enabled, logged-in users will be measured as well (admin users are not measured).',
				'is_expert' => false,
				'is_secret' => true,
				'level' => 1,
				'order' => 3,
			],
			[
				'key' => 'show_metrics_to_logged_in_user',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Show statistics to logged-in users',
				'details' => '',
				'is_expert' => true,
				'is_secret' => true,
				'level' => 1,
				'order' => 4,
			],
			[
				'key' => 'show_metrics_to_owner',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Show statistics to the owner of the album/photo',
				'details' => '',
				'is_expert' => true,
				'is_secret' => true,
				'level' => 1,
				'order' => 5,
			],
			[
				'key' => 'show_metrics_to_public',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Show statistics to the anonymous user',
				'details' => '',
				'is_expert' => true,
				'is_secret' => true,
				'level' => 1,
				'order' => 6,
			],
		];
	}
};
