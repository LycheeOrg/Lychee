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
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('configs', function (Blueprint $table): void {
			$table->string('details', 200)->default('');
			$table->integer('level')->default(0); // 0 for all modifiable, 1 for supported, 2 for plus (if we ever decide to use that)
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('configs', function (Blueprint $table) {
			$table->dropColumn('details');
		});
		Schema::table('configs', function (Blueprint $table) {
			$table->dropColumn('level');
		});
	}
};
