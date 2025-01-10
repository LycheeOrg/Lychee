<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @throws Exception
	 */
	public function up(): void
	{
		DB::table('configs')->where('key', '=', 'api_key')->delete();

		if (!Schema::hasColumn('users', 'token')) {
			Schema::table('users', function (Blueprint $table) {
				$table->char('token', 128)->after('email')->unique()->nullable()->default(null);
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->insert([
			[
				'key' => 'api_key',
				'value' => '',
				'confidentiality' => 3,
				'cat' => 'Admin',
			],
		]);
	}
};
