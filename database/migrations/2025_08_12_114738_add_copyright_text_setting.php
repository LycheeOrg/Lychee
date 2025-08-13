<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	public function up(): void
	{
		DB::table('configs')->insert([
			[
				'key' => 'copyright_text',
				'value' => '',
				'cat' => 'Footer',
				'type_range' => 'string',
				'is_secret' => false,
				'description' => 'Copyright text (replaces default copyright notice)',
				'details' => '',
				'level' => 0,
				'not_on_docker' => false,
				'order' => 5,
				'is_expert' => false,
			],
		]);
	}

	public function down(): void
	{
		DB::table('configs')->where('key', 'copyright_text')->delete();
	}
};
