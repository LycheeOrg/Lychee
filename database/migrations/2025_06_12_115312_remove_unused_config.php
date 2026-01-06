<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	public const CONFIG = 'config';
	public const ENUM = 'left|right';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')->where('key', 'login_button_position')->delete();
		DB::table('configs')->where('key', 'force_32bit_ids')->delete();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->insert([
			[
				'key' => 'login_button_position',
				'value' => 'left',
				'cat' => self::CONFIG,
				'type_range' => self::ENUM,
				'description' => 'Position of the login button',
				'order' => 32767,
				'is_secret' => false,
				'level' => 0,
			],
			[
				'key' => 'force_32bit_ids',
				'value' => '0',
				'cat' => 'Admin',
				'type_range' => 'bool',
				'description' => 'Force 32 bit legacy identifiers in the database',
				'order' => 5,
				'is_secret' => false,
				'level' => 0,
			],
		]);
	}
};
