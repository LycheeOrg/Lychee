<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	public const BOOL = '0|1';
	public const HEADER_SIZE = 'half_screen|full_screen';
	public const CAT_GALLERY = 'Gallery';

	/**
	 * @return array<int,array{key:string,value:string,is_secret:bool,cat:string,type_range:string,description:string,order?:int,not_on_docker?:bool,is_expert?:bool,level?:int}>
	 */
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'album_enhanced_display_enable',
				'value' => '0',
				'cat' => self::CAT_GALLERY,
				'type_range' => self::BOOL,
				'description' => 'Enable enhanced album header features',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 2,
			],
			[
				'key' => 'album_header_size',
				'value' => 'half_screen',
				'cat' => self::CAT_GALLERY,
				'type_range' => self::HEADER_SIZE,
				'description' => 'Global album header image size',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 2,
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
