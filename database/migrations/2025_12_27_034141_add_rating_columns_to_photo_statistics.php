<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	private const COL_RATING_SUM = 'rating_sum';
	private const COL_RATING_COUNT = 'rating_count';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('statistics', function (Blueprint $table) {
			$table->unsignedBigInteger(self::COL_RATING_SUM)->default(0)->comment('Sum of all rating values for this photo');
			$table->unsignedInteger(self::COL_RATING_COUNT)->default(0)->comment('Number of ratings for this photo');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('statistics', function (Blueprint $table) {
			$table->dropColumn([self::COL_RATING_SUM, self::COL_RATING_COUNT]);
		});
	}
};
