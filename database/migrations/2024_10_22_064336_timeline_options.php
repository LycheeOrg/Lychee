<?php

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const TIMELINE = 'Mod Timeline';
	public const STRING_REQ = 'string_required';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'timeline_enable',
				'value' => '1',
				'cat' => self::TIMELINE,
				'type_range' => self::BOOL,
				'description' => 'Enable Timeline',
				'details' => '',
				'is_secret' => false,
				'level' => 0,
			],
			[
				'key' => 'timeline_photo_granularity',
				'value' => 'day',
				'cat' => self::TIMELINE,
				'type_range' => 'year|month|day|hour',
				'description' => 'Timeline granularity for photos',
				'details' => '',
				'is_secret' => false,
				'level' => 1,
			],
			[
				'key' => 'timeline_album_granularity',
				'value' => 'year',
				'cat' => self::TIMELINE,
				'type_range' => 'year|month|day',
				'description' => 'Timeline granularity for albums',
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
				'value' => 'M j',
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
				'value' => 'M j',
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
