<?php

/**
 * SPDX-License-Identifier: MIT
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
				'key' => 'metrics_access',
				'value' => 'admin',
				'cat' => self::CAT,
				'type_range' => 'admin|owner|logged-in users|public',
				'description' => 'Access level for statistics of the album/photo',
				'details' => '',
				'is_expert' => true,
				'is_secret' => true,
				'level' => 1,
				'order' => 4,
			],
			[
				'key' => 'live_metrics_enabled',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Enable live statistics',
				'details' => 'Live metrics provides you an activity history of your gallery.',
				'is_expert' => false,
				'is_secret' => true,
				'level' => 1,
				'order' => 5,
			],
			[
				'key' => 'live_metrics_access',
				'value' => 'admin',
				'cat' => self::CAT,
				'type_range' => 'admin|logged-in users',
				'description' => 'Access level for live metrics',
				'details' => 'If set to "admin", only admins can see the live metrics.',
				'is_expert' => false,
				'is_secret' => true,
				'level' => 1,
				'order' => 6,
			],
			[
				'key' => 'live_metrics_max_time',
				'value' => '30',
				'cat' => self::CAT,
				'type_range' => self::POSITIVE,
				'description' => 'Max age for live metrics in days',
				'details' => 'After this time, the live metrics will be deleted.',
				'is_expert' => false,
				'is_secret' => true,
				'level' => 1,
				'order' => 7,
			],
		];
	}
};
