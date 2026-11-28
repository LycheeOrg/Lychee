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
	/**
	 * Run the migrations.
	 *
	 * User group descriptions are validated by DescriptionRule (DESCRIPTION_MAX_LENGTH, default 1000),
	 * which no longer fits in a varchar(255).
	 */
	public function up(): void
	{
		Schema::table('user_groups', function (Blueprint $table) {
			$table->text('description')->nullable()->change();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('user_groups', function (Blueprint $table) {
			$table->string('description', 255)->nullable()->change();
		});
	}
};
