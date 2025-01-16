<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const TIMELINE = 'Mod Timeline';
	public const STRING_REQ = 'string_required';

	public const SQUARE = 'square';
	public const JUSTIFIED = 'justified';
	public const MASONRY = 'masonry';
	public const GRID = 'grid';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'timeline_photos_enabled',
				'value' => '1',
				'cat' => self::TIMELINE,
				'type_range' => self::BOOL,
				'description' => 'Enable timeline for photos',
				'details' => '',
				'is_secret' => false,
				'level' => 0,
			],
			[
				'key' => 'timeline_photos_public',
				'value' => '0',
				'cat' => self::TIMELINE,
				'type_range' => self::BOOL,
				'description' => 'Allow anonymous users to access the photo timeline',
				'details' => '',
				'is_secret' => false,
				'level' => 0,
			],
			[
				'key' => 'timeline_photos_granularity',
				'value' => 'day',
				'cat' => self::TIMELINE,
				'type_range' => 'year|month|day|hour',
				'description' => 'Timeline granularity for photos',
				'details' => '',
				'is_secret' => false,
				'level' => 1,
			],
			[
				'key' => 'timeline_photos_order',
				'value' => 'taken_at',
				'cat' => self::TIMELINE,
				'type_range' => 'taken_at|created_at',
				'description' => 'Order photos on',
				'details' => 'This determines whether the captured date or the upload date will be used to order the photos.',
				'is_secret' => false,
				'level' => 0,
			],
			[
				'key' => 'timeline_photos_layout',
				'value' => self::SQUARE,
				'cat' => self::TIMELINE,
				'type_range' => self::SQUARE . '|' . self::JUSTIFIED . '|' . self::MASONRY . '|' . self::GRID,
				'description' => 'Photo layout for timeline page',
				'details' => '',
				'is_secret' => false,
				'level' => 0,
			],
			[
				'key' => 'timeline_photos_pagination_limit',
				'value' => '200',
				'cat' => self::TIMELINE,
				'type_range' => self::POSITIVE,
				'description' => 'Number of photos to display per page in timeline',
				'details' => '',
				'is_secret' => false,
				'level' => 0,
			],
			[
				'key' => 'timeline_albums_enabled',
				'value' => '1',
				'cat' => self::TIMELINE,
				'type_range' => self::BOOL,
				'description' => 'Enable timeline for albums',
				'details' => '',
				'is_secret' => false,
				'level' => 0,
			],
			[
				'key' => 'timeline_albums_public',
				'value' => '0',
				'cat' => self::TIMELINE,
				'type_range' => self::BOOL,
				'description' => 'Display the albums timeline for anonymous users',
				'details' => '',
				'is_secret' => false,
				'level' => 0,
			],
			[
				'key' => 'timeline_albums_granularity',
				'value' => 'year',
				'cat' => self::TIMELINE,
				'type_range' => 'year|month|day',
				'description' => 'Timeline granularity for albums',
				'details' => '',
				'is_secret' => false,
				'level' => 1,
			],
			[
				'key' => 'timeline_left_border_enabled',
				'value' => '1',
				'cat' => self::TIMELINE,
				'type_range' => self::BOOL,
				'description' => 'Enable the left border line on timelines',
				'details' => '',
				'is_secret' => false,
				'level' => 1,
			],
			[
				'key' => 'timeline_photo_date_format_year',
				'value' => 'Y',
				'cat' => self::TIMELINE,
				'type_range' => self::STRING_REQ,
				'description' => 'Format the date at year granularity for photos',
				'details' => 'See <a class="underline" href="https://www.php.net/manual/en/datetime.format.php">datetime.format.php</a>',
				'is_secret' => false,
				'level' => 1,
			],
			[
				'key' => 'timeline_photo_date_format_month',
				'value' => 'M Y',
				'cat' => self::TIMELINE,
				'type_range' => self::STRING_REQ,
				'description' => 'Format the date at month granularity for photos',
				'details' => 'See <a class="underline" href="https://www.php.net/manual/en/datetime.format.php">datetime.format.php</a>',
				'is_secret' => false,
				'level' => 1,
			],
			[
				'key' => 'timeline_photo_date_format_day',
				'value' => 'j M Y',
				'cat' => self::TIMELINE,
				'type_range' => self::STRING_REQ,
				'description' => 'Format the date at day granularity for photos',
				'details' => 'See <a class="underline" href="https://www.php.net/manual/en/datetime.format.php">datetime.format.php</a>',
				'is_secret' => false,
				'level' => 1,
			],
			[
				'key' => 'timeline_photo_date_format_hour',
				'value' => 'g:i',
				'cat' => self::TIMELINE,
				'type_range' => self::STRING_REQ,
				'description' => 'Format the date at hour granularity for photos',
				'details' => 'See <a class="underline" href="https://www.php.net/manual/en/datetime.format.php">datetime.format.php</a>',
				'is_secret' => false,
				'level' => 1,
			],
			[
				'key' => 'timeline_album_date_format_year',
				'value' => 'Y',
				'cat' => self::TIMELINE,
				'type_range' => self::STRING_REQ,
				'description' => 'Format the date at year granularity for albums',
				'details' => 'See <a class="underline" href="https://www.php.net/manual/en/datetime.format.php">datetime.format.php</a>',
				'is_secret' => false,
				'level' => 1,
			],
			[
				'key' => 'timeline_album_date_format_month',
				'value' => 'M Y',
				'cat' => self::TIMELINE,
				'type_range' => self::STRING_REQ,
				'description' => 'Format the date at month granularity for albums',
				'details' => 'See <a class="underline" href="https://www.php.net/manual/en/datetime.format.php">datetime.format.php</a>',
				'is_secret' => false,
				'level' => 1,
			],
			[
				'key' => 'timeline_album_date_format_day',
				'value' => 'j M',
				'cat' => self::TIMELINE,
				'type_range' => self::STRING_REQ,
				'description' => 'Format the date at day granularity for albums',
				'details' => 'See <a class="underline" href="https://www.php.net/manual/en/datetime.format.php">datetime.format.php</a>',
				'is_secret' => false,
				'level' => 1,
			],
		];
	}
};
