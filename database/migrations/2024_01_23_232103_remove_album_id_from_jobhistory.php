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
	public const TABLE = 'jobs_history';
	public const COLUMN = 'parent_id';
	public const RANDOM_ID_LENGTH = 24;

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::disableForeignKeyConstraints();
		Schema::table(self::TABLE, function (Blueprint $table) {
			$table->dropColumn(self::COLUMN);
		});
		Schema::enableForeignKeyConstraints();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table) {
			$table->char(self::COLUMN, self::RANDOM_ID_LENGTH)->nullable(true); // parentId = album ID
		});
	}
};
