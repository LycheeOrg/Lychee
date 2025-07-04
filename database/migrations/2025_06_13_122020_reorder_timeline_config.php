<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
	public const CAT = 'cat';
	public const ORDER = 'order';
	public const MAX_ORDER = 32767;

	public const TIMELINE = 'Mod Timeline';
	public const MOD_NSFW = 'Mod NSFW';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$type_range = DB::table('configs')->select('type_range')->where('key', 'home_page_default')->first()->type_range;
		DB::table('configs')->where('key', 'home_page_default')->update(['type_range' => $type_range . '|timeline']);

		DB::table('configs')->where('key', '=', 'hide_nsfw_in_timeline')->update([
			self::CAT => self::MOD_NSFW,
			self::ORDER => self::MAX_ORDER,
		]);

		DB::table('configs')->where('key', '=', 'timeline_page_enabled')->update([self::ORDER => 1]);
		DB::table('configs')->where('key', '=', 'timeline_photos_layout')->update([self::ORDER => 2, 'details' => '']);
		DB::table('configs')->where('key', '=', 'timeline_photos_order')->update([self::ORDER => 3]);
		DB::table('configs')->where('key', '=', 'timeline_photos_pagination_limit')->update([self::ORDER => 4, 'details' => '']);
		DB::table('configs')->where('key', '=', 'timeline_left_border_enabled')->update([self::ORDER => 5]);

		DB::table('configs')->where('key', '=', 'timeline_albums_enabled')->update([self::ORDER => 6]);
		DB::table('configs')->where('key', '=', 'timeline_albums_public')->update([self::ORDER => 7]);
		DB::table('configs')->where('key', '=', 'timeline_albums_granularity')->update([self::ORDER => 8]);

		DB::table('configs')->where('key', '=', 'timeline_photos_enabled')->update([self::ORDER => 9]);
		DB::table('configs')->where('key', '=', 'timeline_photos_public')->update([self::ORDER => 10]);
		DB::table('configs')->where('key', '=', 'timeline_photos_granularity')->update([self::ORDER => 11]);

		DB::table('configs')->where('key', '=', 'timeline_album_date_format_year')->update([self::ORDER => 12]);
		DB::table('configs')->where('key', '=', 'timeline_album_date_format_month')->update([self::ORDER => 13]);
		DB::table('configs')->where('key', '=', 'timeline_album_date_format_day')->update([self::ORDER => 14]);

		DB::table('configs')->where('key', '=', 'timeline_photo_date_format_year')->update([self::ORDER => 15]);
		DB::table('configs')->where('key', '=', 'timeline_photo_date_format_month')->update([self::ORDER => 16]);
		DB::table('configs')->where('key', '=', 'timeline_photo_date_format_day')->update([self::ORDER => 17]);
		DB::table('configs')->where('key', '=', 'timeline_photo_date_format_hour')->update([self::ORDER => 18]);

		DB::table('configs')->where('key', '=', 'timeline_quick_access_date_format_year')->update([self::ORDER => 19, 'is_expert' => true]);
		DB::table('configs')->where('key', '=', 'timeline_quick_access_date_format_month')->update([self::ORDER => 20, 'is_expert' => true]);
		DB::table('configs')->where('key', '=', 'timeline_quick_access_date_format_day')->update([self::ORDER => 21, 'is_expert' => true]);
		DB::table('configs')->where('key', '=', 'timeline_quick_access_date_format_hour')->update([self::ORDER => 22, 'is_expert' => true]);

		$type_range = DB::table('configs')->select('type_range')->where('key', 'home_page_default')->first()->type_range;
		DB::table('configs')->where('key', 'home_page_default')->update(['type_range' => str_replace('|timeline', '', $type_range)]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where(self::CAT, '=', self::TIMELINE)->update([
			self::ORDER => self::MAX_ORDER,
		]);
		DB::table('configs')->where('key', '=', 'hide_nsfw_in_timeline')->update([
			self::CAT => self::TIMELINE,
			self::ORDER => self::MAX_ORDER,
		]);
	}
};
