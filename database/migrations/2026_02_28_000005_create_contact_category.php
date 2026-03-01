<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	public const CAT = 'contact';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('config_categories')->insert([
			[
				'cat' => self::CAT,
				'name' => 'Contact',
				'description' => 'This module allows you to manage contact messages sent by users through the contact form on the website.',
				'order' => 26,
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