<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	public const CAT = 'Mod Rating';
	public const BOOL = '0|1';

	/**
	 * @return array<int,array{key:string,value:string,is_secret:bool,cat:string,type_range:string,description:string,details?:string,order?:int,not_on_docker?:bool,is_expert?:bool,level?:int}>
	 */
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'ratings_enabled',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Enable photo rating',
				'details' => 'Master switch to enable or disable the photo rating feature entirely',
				'level' => 0,
				'not_on_docker' => false,
				'order' => 1,
				'is_expert' => false,
			],
			[
				'key' => 'rating_is_public',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Make photo ratings public',
				'details' => 'Allow all users (including non-logged-in visitors) to see photo ratings',
				'level' => 1,
				'not_on_docker' => false,
				'order' => 2,
				'is_expert' => false,
			],
			[
				'key' => 'rating_show_only_when_user_rated',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Show ratings only after user has rated',
				'details' => 'Only display ratings (user or average) after the user has submitted their own rating',
				'level' => 1,
				'not_on_docker' => false,
				'order' => 3,
				'is_expert' => false,
			],
			[
				'key' => 'rating_show_avg_in_details',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Show average rating in photo details drawer',
				'details' => 'Display average rating and rating count in the photo details sidebar instead of user rating',
				'level' => 0,
				'not_on_docker' => false,
				'order' => 4,
				'is_expert' => false,
			],
			[
				'key' => 'rating_photo_view_mode',
				'value' => 'hover',
				'cat' => self::CAT,
				'type_range' => 'always|hover|never',
				'is_secret' => false,
				'description' => 'Show rating overlay in full photo view',
				'details' => 'Controls visibility of rating overlay: always visible, on hover, or never',
				'level' => 1,
				'not_on_docker' => false,
				'order' => 5,
				'is_expert' => false,
			],
			[
				'key' => 'rating_show_avg_in_photo_view',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Display average rating in full photo view',
				'details' => 'Display average rating when viewing a photo in full-size mode instead of the user rating',
				'level' => 1,
				'not_on_docker' => false,
				'order' => 6,
				'is_expert' => false,
			],
			[
				'key' => 'rating_album_view_mode',
				'value' => 'hover',
				'cat' => self::CAT,
				'type_range' => 'always|hover|never',
				'is_secret' => false,
				'description' => 'Show rating on photo thumbnails in album view.',
				'details' => 'Controls visibility of rating on thumbnails: always visible, on hover, or never',
				'level' => 1,
				'not_on_docker' => false,
				'order' => 7,
				'is_expert' => false,
			],
			[
				'key' => 'rating_show_avg_in_album_view',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Display average rating on photo thumbnails',
				'details' => 'Display average rating on photo thumbnails in album view instead of the user rating',
				'level' => 0,
				'not_on_docker' => false,
				'order' => 8,
				'is_expert' => false,
			],
		];
	}

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		// No mercy
		$this->down();

		DB::table('config_categories')->insert([
			[
				'cat' => self::CAT,
				'name' => 'Photo star rating',
				'description' => 'This modules enable rating of photos. The user can set a rating from 1 to 5 stars per photo. The average rating is displayed where configured.',
				'order' => 24,
			],
		]);

		DB::table('configs')->insert($this->getConfigs());
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		$keys = collect($this->getConfigs())->map(fn ($v) => $v['key'])->all();
		DB::table('configs')->whereIn('key', $keys)->delete();
		DB::table('config_categories')->where('cat', self::CAT)->delete();
	}
};
