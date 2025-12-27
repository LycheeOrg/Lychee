<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	public const CAT = 'Gallery';

	private function getConfigs(): array
	{
		return [
			[
				'key' => 'ratings_enabled',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => '0|1',
				'is_secret' => false,
				'description' => 'Enable photo rating feature',
				'details' => 'Master switch to enable or disable the photo rating feature entirely',
				'level' => 0,
				'not_on_docker' => false,
				'order' => 500,
				'is_expert' => false,
			],
			[
				'key' => 'rating_show_avg_in_details',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => '0|1',
				'is_secret' => false,
				'description' => 'Show average rating in photo details drawer',
				'details' => 'Display average rating and rating count in the photo details sidebar',
				'level' => 0,
				'not_on_docker' => false,
				'order' => 501,
				'is_expert' => false,
			],
			[
				'key' => 'rating_show_avg_in_photo_view',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => '0|1',
				'is_secret' => false,
				'description' => 'Show average rating in full photo view',
				'details' => 'Display average rating when viewing a photo in full-size mode',
				'level' => 0,
				'not_on_docker' => false,
				'order' => 502,
				'is_expert' => false,
			],
			[
				'key' => 'rating_photo_view_mode',
				'value' => 'hover',
				'cat' => self::CAT,
				'type_range' => 'always|hover|hidden',
				'is_secret' => false,
				'description' => 'When to show rating overlay in full photo view',
				'details' => 'Controls visibility of rating overlay: always visible, on hover, or hidden',
				'level' => 0,
				'not_on_docker' => false,
				'order' => 503,
				'is_expert' => false,
			],
			[
				'key' => 'rating_show_avg_in_album_view',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => '0|1',
				'is_secret' => false,
				'description' => 'Show average rating on photo thumbnails',
				'details' => 'Display average rating on photo thumbnails in album view',
				'level' => 0,
				'not_on_docker' => false,
				'order' => 504,
				'is_expert' => false,
			],
			[
				'key' => 'rating_album_view_mode',
				'value' => 'hover',
				'cat' => self::CAT,
				'type_range' => 'always|hover|hidden',
				'is_secret' => false,
				'description' => 'When to show rating on photo thumbnails',
				'details' => 'Controls visibility of rating on thumbnails: always visible, on hover, or hidden',
				'level' => 0,
				'not_on_docker' => false,
				'order' => 505,
				'is_expert' => false,
			],
		];
	}

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')->insert($this->getConfigs());
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		$keys = collect($this->getConfigs())->map(fn ($v) => $v['key'])->all();
		DB::table('configs')->whereIn('key', $keys)->delete();
	}
};
