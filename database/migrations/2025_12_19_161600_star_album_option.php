<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const MOD_GALLERY = 'Smart Albums';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'enable_smart_album_per_owner',
				'value' => '0',
				'cat' => self::MOD_GALLERY,
				'type_range' => self::BOOL,
				'description' => 'Only display pictures owned by the user in smart albums.',
				'details' => 'This setting is only applied to logged-in users.',
				'is_secret' => false,
				'is_expert' => false,
				'order' => 12,
				'not_on_docker' => false,
				'level' => 0,
			],
		];
	}
};
