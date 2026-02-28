<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 *
	 * Shifts all existing size_variants.type values by +1 to make room for RAW = 0.
	 * Uses a two-phase approach to avoid unique constraint violations on (photo_id, type):
	 * Phase 1: Shift to temporary range (type + 10)
	 * Phase 2: Shift down to final range (type - 9)
	 * Result: all values incremented by 1.
	 */
	public function up(): void
	{
		// Phase 1: shift all existing values to a temporary range
		DB::table('size_variants')
			->where('type', '>=', 0)
			->update(['type' => DB::raw('type + 10')]);

		// Phase 2: shift from temporary range to final range
		DB::table('size_variants')
			->where('type', '>=', 10)
			->update(['type' => DB::raw('type - 9')]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// Reverse Phase 1: shift to temporary range
		DB::table('size_variants')
			->where('type', '>=', 1)
			->update(['type' => DB::raw('type + 9')]);

		// Reverse Phase 2: shift from temporary range to original range
		DB::table('size_variants')
			->where('type', '>=', 10)
			->update(['type' => DB::raw('type - 10')]);
	}
};
