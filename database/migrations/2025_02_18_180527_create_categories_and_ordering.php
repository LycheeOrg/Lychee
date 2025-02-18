<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class() extends Migration {
	public const COL = 'order';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('configs', function (Blueprint $table): void {
			$table->unsignedSmallInteger(self::COL)->default(65535)->after('not_on_docker');
		});

		DB::table('configs')->where('key', 'config_sort_albums_by')->update([self::COL => 0]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('config_categories')->truncate();

		Schema::table('configs', function (Blueprint $table) {
			$table->dropColumn(self::COL);
		});
	}
};
