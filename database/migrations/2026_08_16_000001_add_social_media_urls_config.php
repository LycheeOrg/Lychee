<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const FOOTER = 'Footer';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'sm_pinterest_url',
				'value' => '',
				'cat' => self::FOOTER,
				'type_range' => self::STRING,
				'description' => 'Url of pinterest profile',
				'details' => '',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 5,
			],
			[
				'key' => 'sm_deviantart_url',
				'value' => '',
				'cat' => self::FOOTER,
				'type_range' => self::STRING,
				'description' => 'Url of deviantart profile',
				'details' => '',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 6,
			],
			[
				'key' => 'sm_tumblr_url',
				'value' => '',
				'cat' => self::FOOTER,
				'type_range' => self::STRING,
				'description' => 'Url of tumblr profile',
				'details' => '',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 7,
			],
			[
				'key' => 'sm_500px_url',
				'value' => '',
				'cat' => self::FOOTER,
				'type_range' => self::STRING,
				'description' => 'Url of 500px profile',
				'details' => '',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 8,
			],
			[
				'key' => 'sm_pixelfeed_url',
				'value' => '',
				'cat' => self::FOOTER,
				'type_range' => self::STRING,
				'description' => 'Url of pixelfeed profile',
				'details' => '',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 9,
			],
			[
				'key' => 'sm_discord_url',
				'value' => '',
				'cat' => self::FOOTER,
				'type_range' => self::STRING,
				'description' => 'Url of discord server',
				'details' => '',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 10,
			],
			[
				'key' => 'sm_reddit_url',
				'value' => '',
				'cat' => self::FOOTER,
				'type_range' => self::STRING,
				'description' => 'Url of reddit profile',
				'details' => '',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 11,
			],
		];
	}
};
