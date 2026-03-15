<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
	public function up(): void
	{
		DB::table('base_albums')
			->where('sorting_col', '=', 'is_starred')
			->update([
				'sorting_col' => 'is_highlighted',
			]);
	}

	public function down(): void
	{
		DB::table('base_albums')
			->where('sorting_col', '=', 'is_starred')
			->update([
				'sorting_col' => 'is_highlighted',
			]);
	}
};
