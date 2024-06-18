<?php

declare(strict_types=1);

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const SMART_ALBUMS = 'Smart Albums';
	public const BOOL = '0|1';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'enable_unsorted',
				'value' => '1',
				'is_secret' => false,
				'cat' => self::SMART_ALBUMS,
				'type_range' => self::BOOL,
				'description' => 'Enable Unsorted smart album. Warning! Disabling this will make pictures without an album invisible.',
			],
			[
				'key' => 'enable_starred',
				'value' => '1',
				'is_secret' => false,
				'cat' => self::SMART_ALBUMS,
				'type_range' => self::BOOL,
				'description' => 'Enable Starred smart album.',
			],
			[
				'key' => 'enable_recent',
				'value' => '1',
				'is_secret' => false,
				'cat' => self::SMART_ALBUMS,
				'type_range' => self::BOOL,
				'description' => 'Enable Recent uploads smart album.',
			],
			[
				'key' => 'enable_on_this_day',
				'value' => '1',
				'is_secret' => false,
				'cat' => self::SMART_ALBUMS,
				'type_range' => self::BOOL,
				'description' => 'Enable On this day smart album.',
			],
		];
	}
};
