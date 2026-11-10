<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

/**
 * Feature 095 (FR-095-01): resting label formats of the date scrubber rail.
 * Year, month and hour are Supporter Edition; the day format is free so every
 * instance can set its day order (Q-095-02). Ordered right after the
 * quick-access date formats (Q-095-06).
 */
return new class() extends BaseConfigMigration {
	public const TIMELINE = 'Mod Timeline';
	public const STRING_REQ = 'string_required';
	private const DETAILS = 'See <a class="underline" href="https://www.php.net/manual/en/datetime.format.php">datetime.format.php</a>. The rail is 64 px wide: longer labels are cut.';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'date_scrubber_format_year',
				'value' => 'Y',
				'cat' => self::TIMELINE,
				'type_range' => self::STRING_REQ,
				'description' => 'Date scrubber label at year granularity',
				'details' => self::DETAILS,
				'is_secret' => false,
				'level' => 1,
				'order' => 23,
			],
			[
				'key' => 'date_scrubber_format_month',
				'value' => 'M',
				'cat' => self::TIMELINE,
				'type_range' => self::STRING_REQ,
				'description' => 'Date scrubber label at month granularity',
				'details' => self::DETAILS,
				'is_secret' => false,
				'level' => 1,
				'order' => 24,
			],
			[
				'key' => 'date_scrubber_format_day',
				'value' => 'd/m',
				'cat' => self::TIMELINE,
				'type_range' => self::STRING_REQ,
				'description' => 'Date scrubber label at day granularity',
				'details' => self::DETAILS,
				'is_secret' => false,
				'level' => 0,
				'order' => 25,
			],
			[
				'key' => 'date_scrubber_format_hour',
				'value' => 'H:00',
				'cat' => self::TIMELINE,
				'type_range' => self::STRING_REQ,
				'description' => 'Date scrubber label at hour granularity',
				'details' => self::DETAILS,
				'is_secret' => false,
				'level' => 1,
				'order' => 26,
			],
		];
	}
};
