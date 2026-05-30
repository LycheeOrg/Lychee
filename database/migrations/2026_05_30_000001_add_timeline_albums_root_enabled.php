<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	public const CAT = 'Mod Timeline';
	public const BOOL = '0|1';

	public function up(): void
	{
		$val = DB::table('configs')->where('key', 'timeline_albums_enabled')->select('value')->get();
		DB::table('configs')->insert([
			'key' => 'timeline_albums_root_enabled',
			'value' => $val->isEmpty() ? '0' : $val[0]->value,
			'cat' => self::CAT,
			'type_range' => self::BOOL,
			'description' => 'Enable timeline for albums at root',
			'details' => '',
			'is_secret' => false,
			'is_expert' => false,
			'level' => 0,
			'order' => 11,
		]);
		DB::table('configs')->where('key', 'timeline_albums_enabled')->update(['details' => 'Globally enable albums timelines in each albums. This can also be disabled/enabled per album.']);
	}

	public function down(): void
	{
		DB::table('configs')->where('key', 'timeline_albums_root_enabled')->delete();
		DB::table('configs')->where('key', 'timeline_albums_enabled')->update(['details' => 'Globally enable albums timelines in each albums (and root). This can also be disabled/enabled per album.']);
	}
};
