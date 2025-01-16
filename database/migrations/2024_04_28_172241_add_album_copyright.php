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
	public const TABLE = 'base_albums';
	public const COLUMN = 'copyright';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table) {
			$table->string(self::COLUMN, 300)->nullable()->default(null);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table) {
			$table->dropColumn(self::COLUMN);
		});
	}
};
