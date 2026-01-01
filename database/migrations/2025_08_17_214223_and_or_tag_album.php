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
	private const COND = 'is_and';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		if (!Schema::hasColumn('tag_albums', self::COND)) {
			Schema::table('tag_albums', function (Blueprint $table) {
				$table->boolean(self::COND)->nullable(false)->default(true)->after('id');
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if (Schema::hasColumn('tag_albums', self::COND)) {
			Schema::table('tag_albums', function (Blueprint $table) {
				$table->dropColumn(self::COND);
			});
		}
	}
};
