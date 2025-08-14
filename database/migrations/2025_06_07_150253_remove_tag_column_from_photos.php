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
	public const TAGS = 'tags';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		if (Schema::hasColumn('photos', self::TAGS)) {
			Schema::table('photos', function (Blueprint $table) {
				$table->dropColumn(self::TAGS);
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if (!Schema::hasColumn('photos', self::TAGS)) {
		Schema::table('photos', function (Blueprint $table) {
			$table->text('tags')->nullable()->after('description');
		});
	}
	}
};
