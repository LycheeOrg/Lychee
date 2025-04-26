<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void
	{
		$landing_background = DB::table('configs')->where('key', 'landing_background')->first()->value;
		DB::table('configs')->where('key', 'landing_background_landscape')->update(['value' => $landing_background]);
		DB::table('configs')->where('key', 'landing_background_portrait')->update(['value' => $landing_background]);
		DB::table('configs')->where('key', 'landing_background')->delete();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(): void
	{
		DB::table('configs')->insert([
			'key' => 'landing_background',
			'value' => DB::table('configs')->where('key', 'landing_background_landscape')->first()->value,
			'cat' => 'Mod Welcome',
			'type_range' => 'string',
			'description' => 'URL of background image',
			'details' => '',
			'is_expert' => false,
			'is_secret' => true,
			'level' => 0,
			'order' => 3,
		]);
	}
};
