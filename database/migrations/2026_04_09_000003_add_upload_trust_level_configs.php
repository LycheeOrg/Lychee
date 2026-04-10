<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'default_user_trust_level',
				'value' => 'trusted',
				'cat' => 'Admin',
				'type_range' => 'check|monitor|trusted',
				'description' => 'Default upload trust level assigned to newly created users.',
				'details' => 'check: uploads require admin approval. monitor: reserved (behaves as trusted). trusted: uploads are immediately public.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
			],
			[
				'key' => 'guest_upload_trust_level',
				'value' => 'check',
				'cat' => 'Admin',
				'type_range' => 'check|monitor|trusted',
				'description' => 'Upload trust level applied to anonymous (guest) uploads.',
				'details' => 'check: guest uploads require admin approval. monitor: reserved (behaves as trusted). trusted: guest uploads are immediately public.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
			],
		];
	}
};
