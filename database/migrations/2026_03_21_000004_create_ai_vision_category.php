<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	public const CAT = 'AI Vision';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('config_categories')->insert([
			[
				'cat' => self::CAT,
				'name' => 'AI Vision',
				'description' => 'This module integrates with an external AI service to provide facial recognition, person management, and automatic face scanning capabilities.',
				'order' => 27,
			],
		]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('config_categories')->where('cat', self::CAT)->delete();
	}
};
