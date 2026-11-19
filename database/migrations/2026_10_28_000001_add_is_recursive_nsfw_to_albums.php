<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 *
	 * The flag is true when the album or one of its ancestors is NSFW: every
	 * album in the nested-set range of an NSFW album is flagged. Ranges nested
	 * in an already flagged one are skipped.
	 */
	public function up(): void
	{
		Schema::table('albums', function (Blueprint $table) {
			$table->boolean('is_recursive_nsfw')->nullable(false)->default(false)->after('_rgt');
		});

		$ranges = DB::table('albums')
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->where('base_albums.is_nsfw', '=', true)
			->orderBy('albums._lft')
			->get(['albums._lft', 'albums._rgt']);

		$flagged_right = 0;
		foreach ($ranges as $range) {
			if ((int) $range->_lft < $flagged_right) {
				continue;
			}
			DB::table('albums')
				->where('_lft', '>=', (int) $range->_lft)
				->where('_rgt', '<=', (int) $range->_rgt)
				->update(['is_recursive_nsfw' => true]);
			$flagged_right = (int) $range->_rgt;
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('albums', function (Blueprint $table) {
			$table->dropColumn('is_recursive_nsfw');
		});
	}
};
