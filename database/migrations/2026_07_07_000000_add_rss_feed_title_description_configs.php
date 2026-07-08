<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const MOD_RSS = 'Mod RSS';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'rss_title',
				// Empty means "use the default": the middleware only overrides the
				// feed channel title when this is non-empty, so config/feed.php stays
				// the single source of truth for the default. See SetRssFeedMeta.
				'value' => '',
				'cat' => self::MOD_RSS,
				'type_range' => self::STRING,
				'description' => 'Feed Title',
				'details' => 'Shown as the channel title in the RSS feed. Leave blank to use the default.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 3,
			],
			[
				'key' => 'rss_description',
				// Empty means "use the default"; see the note on rss_title above.
				'value' => '',
				'cat' => self::MOD_RSS,
				'type_range' => self::STRING,
				'description' => 'Feed Description',
				'details' => 'Shown as the channel description in the RSS feed. Leave blank to use the default.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 4,
			],
		];
	}
};
