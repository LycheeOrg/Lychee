<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	private const TABLE_NAME = 'access_permissions';
	private const TABLE_CONFIGS = 'configs';

	public const SMART_ALBUMS = 'Smart Albums';

	private const BASE_ALBUM_ID = 'base_album_id';
	private const GRANTS_FULL_PHOTO_ACCESS = 'grants_full_photo_access';
	private const GRANTS_DOWNLOAD = 'grants_download';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$default_full_photo_access = DB::table('configs')->where('key', '=', 'grants_full_photo_access')->first('value')?->value === '1';
		$default_download = DB::table('configs')->where('key', '=', 'grants_download')->first('value')?->value === '1';

		$val = DB::table(self::TABLE_CONFIGS)->where('key', '=', 'public_recent')->first('value')?->value;
		if ($val === '1') {
			DB::table(self::TABLE_NAME)->insert([[
				self::BASE_ALBUM_ID => 'recent',
				self::GRANTS_FULL_PHOTO_ACCESS => $default_full_photo_access,
				self::GRANTS_DOWNLOAD => $default_download],
			]);
		}

		$val = DB::table(self::TABLE_CONFIGS)->where('key', '=', 'public_starred')->first('value')?->value;
		if ($val === '1') {
			DB::table(self::TABLE_NAME)->insert([[
				self::BASE_ALBUM_ID => 'starred',
				self::GRANTS_FULL_PHOTO_ACCESS => $default_full_photo_access,
				self::GRANTS_DOWNLOAD => $default_download],
			]);
		}
		$val = DB::table(self::TABLE_CONFIGS)->where('key', '=', 'public_on_this_day')->first('value')?->value;

		if ($val === '1') {
			DB::table(self::TABLE_NAME)->insert([[
				self::BASE_ALBUM_ID => 'on_this_day',
				self::GRANTS_FULL_PHOTO_ACCESS => $default_full_photo_access,
				self::GRANTS_DOWNLOAD => $default_download],
			]);
		}

		DB::table(self::TABLE_CONFIGS)->whereIn('key', ['public_recent', 'public_starred', 'public_on_this_day'])->delete();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		$is_public_recent = DB::table(self::TABLE_NAME)->where(self::BASE_ALBUM_ID, '=', 'recent')->first() !== null;
		$is_public_starred = DB::table(self::TABLE_NAME)->where(self::BASE_ALBUM_ID, '=', 'starred')->first() !== null;
		$is_public_on_this_day = DB::table(self::TABLE_NAME)->where(self::BASE_ALBUM_ID, '=', 'on_this_day')->first() !== null;

		DB::table(self::TABLE_CONFIGS)->insert([
			[
				'key' => 'public_recent',
				'value' => $is_public_recent ? '1' : '0',
				'cat' => self::SMART_ALBUMS,
				'type_range' => '0|1',
				'confidentiality' => '0',
				'description' => 'Make Recent smart album accessible to anonymous users',
			],
			[
				'key' => 'public_starred',
				'value' => $is_public_starred ? '1' : '0',
				'cat' => self::SMART_ALBUMS,
				'type_range' => '0|1',
				'confidentiality' => '0',
				'description' => 'Make Starred smart album accessible to anonymous users',
			],
			[
				'key' => 'public_on_this_day',
				'value' => $is_public_on_this_day ? '1' : '0',
				'cat' => self::SMART_ALBUMS,
				'type_range' => '0|1',
				'confidentiality' => '0',
				'description' => 'Make "On This Day" smart album accessible to anonymous users',
			],
		]);

		DB::table(self::TABLE_NAME)->whereIn(self::BASE_ALBUM_ID, ['recent', 'starred', 'on_this_day'])->delete();
	}
};
