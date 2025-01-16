<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	private const ALBUM = 'albums';
	private const SORT_COLUMN_NAME = 'album_sorting_col';
	private const SORT_COLUMN_ORDER = 'album_sorting_order';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table(self::ALBUM, function ($table) {
			$table->string(self::SORT_COLUMN_NAME, 30)->nullable()->default(null)->after('license');
		});
		Schema::table(self::ALBUM, function ($table) {
			$table->string(self::SORT_COLUMN_ORDER, 10)->nullable()->default(null)->after(self::SORT_COLUMN_NAME);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table(self::ALBUM, function (Blueprint $table) {
			$table->dropColumn(self::SORT_COLUMN_NAME);
		});
		Schema::table(self::ALBUM, function (Blueprint $table) {
			$table->dropColumn(self::SORT_COLUMN_ORDER);
		});
	}
};
