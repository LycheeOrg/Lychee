<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	private const TAKESTAMP = 'takestamp';
	private const TAKEN_AT = 'taken_at';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')->where('value', '=', self::TAKESTAMP)->update(['value' => self::TAKEN_AT]);
		DB::table('albums')->where('sorting_col', '=', self::TAKESTAMP)->update(['sorting_col' => self::TAKEN_AT]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('value', '=', self::TAKEN_AT)->update(['value' => self::TAKESTAMP]);
		DB::table('albums')->where('sorting_col', '=', self::TAKEN_AT)->update(['sorting_col' => self::TAKESTAMP]);
	}
};
