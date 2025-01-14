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
		DB::table('photos')->where('small', 'x')->update(['small' => '']);
		DB::table('photos')->where('small2x', 'x')->update(['small2x' => '']);
		DB::table('photos')->where('medium', 'x')->update(['medium' => '']);
		DB::table('photos')->where('medium2x', 'x')->update(['medium2x' => '']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// There is no undo
	}
};
