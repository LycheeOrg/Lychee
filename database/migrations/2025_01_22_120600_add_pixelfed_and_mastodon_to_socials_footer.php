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
			[
				'key' => 'sm_pixelfed_url',
				'value' => '', // default -> empty URL
				'cat' => 'Footer',
				'type_range' => self::STRING,
				'description' => 'Url of pixelfed profile',
				'details' => '',
				'is_secret' => true,
				'level' => 0,
			],
			[
				'key' => 'sm_mastodon_url',
				'value' => '', // default -> empty URL
				'cat' => 'Footer',
				'type_range' => self::STRING,
				'description' => 'Url of mastodon profile',
				'details' => '',
				'is_secret' => true,
				'level' => 0,
			],
		];
	}
};
