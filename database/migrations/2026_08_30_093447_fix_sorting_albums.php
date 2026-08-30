<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
	private const REMOVED_VALUES = ['title_strict', 'description', 'description_strict'];

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')
			->whereIn('album_sorting_col', self::REMOVED_VALUES)
			->update(['album_sorting_col' => 'title']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// no coming back.
		// Sowwy
	}
};
