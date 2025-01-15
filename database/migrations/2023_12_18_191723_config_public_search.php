<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	public const MOD_SEARCH = 'Mod Search';
	public const BOOL = '0|1';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')->where('key', 'public_search')->update(
			['key' => 'search_public', 'cat' => self::MOD_SEARCH]);
		DB::table('configs')->where('key', 'public_photos_hidden')->delete();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', 'search_public')->update(
			['key' => 'public_search', 'cat' => 'config']);
		DB::table('configs')->insert([
			[
				'key' => 'public_photos_hidden',
				'value' => '1',
				'confidentiality' => 0,
				'cat' => 'config',
				'type_range' => self::BOOL,
				'description' => 'Keep singular public pictures hidden from search results, smart albums & tag albums',
			],
		]);
	}
};
