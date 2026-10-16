<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

/**
 * Feature 095 (FR-095-08): hour section headers of a photo listing spanning
 * several days use this format; on a single day they keep
 * `timeline_photo_date_format_hour` (Q-095-08). Order 18 with the hour
 * format, so it sorts right after it by key.
 */
return new class() extends BaseConfigMigration {
	public const TIMELINE = 'Mod Timeline';
	public const STRING_REQ = 'string_required';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'timeline_photo_date_format_hour_multi_day',
				'value' => 'j M Y H:00',
				'cat' => self::TIMELINE,
				'type_range' => self::STRING_REQ,
				'description' => 'Format the date at hour granularity for photos spanning several days',
				'details' => 'See <a class="underline" href="https://www.php.net/manual/en/datetime.format.php">datetime.format.php</a>. Used when the photos of a view span several days; on a single day the hour format above is used.',
				'is_secret' => false,
				'level' => 1,
				'order' => 18,
			],
		];
	}
};
