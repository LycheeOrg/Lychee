<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('configs', function (Blueprint $table) {
			$table->renameColumn('details', 'details_old');
		});
		Schema::table('configs', function (Blueprint $table) {
			$table->text('details')->nullable(true)->after('details_old')->comment('Details for the config, can be used to store extra informations for the users.');
		});
		// Migrate old details to new details column
		DB::table('configs')->update(['details' => DB::raw('details_old')]);
		Schema::table('configs', function (Blueprint $table) {
			$table->dropColumn('details_old');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('configs', function (Blueprint $table) {
			$table->renameColumn('details', 'details_old');
		});
		Schema::table('configs', function (Blueprint $table) {
			$table->string('details', 200)->after('details_old')->default('')->comment('Details for the config, can be used to store extra informations for the users.');
		});
		// Migrate old details to new details column
		DB::table('configs')->where(DB::raw('LENGTH(details_old)'), '<', '200')->update(['details' => DB::raw('details_old')]);
		Schema::table('configs', function (Blueprint $table) {
			$table->dropColumn('details_old');
		});
	}
};
