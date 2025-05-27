<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
	public const UNJUSTIFIED = 'unjustified'; // ! Legacy
	public const JUSTIFIED = 'justified';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')->where('key', '=', 'layout')->where('value', '=', self::UNJUSTIFIED)->update(['value' => self::JUSTIFIED]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// Nothing to do.
	}
};
