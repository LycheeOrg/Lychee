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
		DB::table('configs')->insert([
			[
				'key' => 'username',
				'value' => '',
				'confidentiality' => '4',
				'cat' => 'Admin',
				'type_range' => 'string_required',
			],
			[
				'key' => 'password',
				'value' => '',
				'confidentiality' => '4',
				'cat' => 'Admin',
				'type_range' => 'string_required',
			],
		]
		);
	}
};
