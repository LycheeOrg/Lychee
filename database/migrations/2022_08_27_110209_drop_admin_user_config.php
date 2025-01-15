<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')
			->whereIn('key', ['username', 'password'])
			->delete();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		defined('STRING_REQ') or define('STRING_REQ', 'string_required');

		DB::table('configs')->insert([
			[
				'key' => 'username',
				'value' => '',
				'confidentiality' => '4',
				'cat' => 'Admin',
				'type_range' => STRING_REQ,
			],
			[
				'key' => 'password',
				'value' => '',
				'confidentiality' => '4',
				'cat' => 'Admin',
				'type_range' => STRING_REQ,
			],
		]
		);
	}
};
