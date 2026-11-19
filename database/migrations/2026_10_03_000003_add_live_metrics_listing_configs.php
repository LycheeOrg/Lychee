<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'Mod Pro';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'live_metrics_result_limit',
				'value' => '1000',
				'cat' => self::CAT,
				'type_range' => self::POSITIVE,
				'description' => 'Max number of live metrics entries',
				'details' => 'Events of the same kind on the same item within one minute count as one entry. Older entries beyond this limit are not shown.',
				'is_expert' => true,
				'is_secret' => true,
				'level' => 1,
				'order' => 8,
			],
			[
				'key' => 'live_metrics_cleanup',
				'value' => 'deferred',
				'cat' => self::CAT,
				'type_range' => 'deferred|sync|disabled',
				'description' => 'Deletion of expired live metrics',
				'details' => 'deferred: deleted after the live metrics are sent. sync: deleted before they are read. disabled: never deleted (expired entries are still hidden).',
				'is_expert' => true,
				'is_secret' => true,
				'level' => 1,
				'order' => 9,
			],
		];
	}
};
