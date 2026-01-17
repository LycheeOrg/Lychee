<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	public const CAT = 'Mod Smart Albums';
	public const BOOL = '0|1';
	public const POSITIVE = 'positive';

	/**
	 * @return array<int,array{key:string,value:string,is_secret:bool,cat:string,type_range:string,description:string,details?:string,order?:int,not_on_docker?:bool,is_expert?:bool,level?:int}>
	 */
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'enable_unrated',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Enable Unrated smart album',
				'details' => 'Show smart album containing photos without any ratings',
				'level' => 0,
				'not_on_docker' => false,
				'order' => 20,
				'is_expert' => false,
			],
			[
				'key' => 'enable_1_star',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Enable 1 Star smart album',
				'details' => 'Show smart album containing photos rated 1.0 to <2.0 stars',
				'level' => 0,
				'not_on_docker' => false,
				'order' => 21,
				'is_expert' => false,
			],
			[
				'key' => 'enable_2_stars',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Enable 2 Stars smart album',
				'details' => 'Show smart album containing photos rated 2.0 to <3.0 stars',
				'level' => 0,
				'not_on_docker' => false,
				'order' => 22,
				'is_expert' => false,
			],
			[
				'key' => 'enable_3_stars',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Enable 3+ Stars smart album',
				'details' => 'Show smart album containing photos rated 3.0 stars or higher',
				'level' => 0,
				'not_on_docker' => false,
				'order' => 23,
				'is_expert' => false,
			],
			[
				'key' => 'enable_4_stars',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Enable 4+ Stars smart album',
				'details' => 'Show smart album containing photos rated 4.0 stars or higher',
				'level' => 0,
				'not_on_docker' => false,
				'order' => 24,
				'is_expert' => false,
			],
			[
				'key' => 'enable_5_stars',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Enable 5 Stars smart album',
				'details' => 'Show smart album containing photos with perfect 5.0 rating',
				'level' => 0,
				'not_on_docker' => false,
				'order' => 25,
				'is_expert' => false,
			],
			[
				'key' => 'enable_best_pictures',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Enable Best Pictures smart album (Lychee SE)',
				'details' => 'Show smart album containing top-rated photos. Requires Lychee SE license.',
				'level' => 0,
				'not_on_docker' => false,
				'order' => 26,
				'is_expert' => false,
			],
			[
				'key' => 'best_pictures_count',
				'value' => '100',
				'cat' => self::CAT,
				'type_range' => self::POSITIVE,
				'is_secret' => false,
				'description' => 'Best Pictures album photo count',
				'details' => 'Number of top-rated photos to show in Best Pictures album. Photos tied at the cutoff are included.',
				'level' => 0,
				'not_on_docker' => false,
				'order' => 27,
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
