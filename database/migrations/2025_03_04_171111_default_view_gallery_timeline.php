<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const TIMELINE = 'Mod Timeline';
	public const CONFIG = 'config';
	public const STRING_REQ = 'string_required';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'timeline_page_enabled',
				'value' => '1',
				'cat' => self::TIMELINE,
				'type_range' => self::BOOL,
				'description' => 'Enable timeline page',
				'details' => '',
				'is_secret' => false,
				'level' => 0,
			],
			[
				'key' => 'home_page_default',
				'value' => 'gallery',
				'cat' => self::CONFIG,
				'type_range' => 'timeline|gallery',
				'description' => 'Default home page after landing',
				'details' => '',
				'is_secret' => false,
				'level' => 0,
			],
			[
				'key' => 'timeline_quick_access_date_format_year',
				'value' => 'Y',
				'cat' => self::TIMELINE,
				'type_range' => self::STRING_REQ,
				'description' => 'Format the quick access year granularity in the timeline page',
				'details' => 'See <a class="underline" href="https://www.php.net/manual/en/datetime.format.php">datetime.format.php</a>',
				'is_secret' => false,
				'level' => 1,
			],
			[
				'key' => 'timeline_quick_access_date_format_month',
				'value' => 'M',
				'cat' => self::TIMELINE,
				'type_range' => self::STRING_REQ,
				'description' => 'Format the quick access month granularity in the timeline page',
				'details' => 'See <a class="underline" href="https://www.php.net/manual/en/datetime.format.php">datetime.format.php</a>',
				'is_secret' => false,
				'level' => 1,
			],
			[
				'key' => 'timeline_quick_access_date_format_day',
				'value' => 'j M',
				'cat' => self::TIMELINE,
				'type_range' => self::STRING_REQ,
				'description' => 'Format the quick access day granularity in the timeline page',
				'details' => 'See <a class="underline" href="https://www.php.net/manual/en/datetime.format.php">datetime.format.php</a>',
				'is_secret' => false,
				'level' => 1,
			],
			[
				'key' => 'timeline_quick_access_date_format_hour',
				'value' => 'h M, g:i',
				'cat' => self::TIMELINE,
				'type_range' => self::STRING_REQ,
				'description' => 'Format the quick access hour granularity in the timeline page',
				'details' => 'See <a class="underline" href="https://www.php.net/manual/en/datetime.format.php">datetime.format.php</a>',
				'is_secret' => false,
				'level' => 1,
			],
		];
	}
};